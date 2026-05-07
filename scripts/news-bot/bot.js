import "dotenv/config";
import fs from "node:fs";
import path from "node:path";

import { createApiService } from "./apiService.js";
import { createDedupeService } from "./dedupeService.js";
import { createImageService } from "./imageService.js";
import { createRssService } from "./rssService.js";

const DEFAULT_STATE_PATH = "storage/app/news-bot/processed-news.json";
const DEFAULT_LOG_PATH = "storage/logs/news-bot.log";
const DEFAULT_TIMEOUT_MS = 15000;
const DEFAULT_PER_SOURCE_LIMIT = 10;
const DEFAULT_MAX_CONTENT_CHARS = 12000;
const DEFAULT_MIN_CONTENT_CHARS = 280;

async function main() {
    const config = getConfig();
    const logger = createLogger(config.logPath);

    logger.info("News bot started", {
        sourceCount: config.sources.length,
        dryRun: config.dryRun,
    });

    const rssService = createRssService({
        logger,
        maxContentChars: config.maxContentChars,
        minContentChars: config.minContentChars,
        timeoutMs: config.timeoutMs,
    });

    const dedupeService = await createDedupeService({
        logger,
        stateFilePath: config.statePath,
    });

    const apiService = createApiService({
        logger,
        baseUrl: config.laravelApiUrl,
        token: config.laravelApiToken,
        timeoutMs: config.timeoutMs,
    });

    const imageService = createImageService({
        logger,
        timeoutMs: config.timeoutMs,
    });

    const categories = [...new Set(config.sources.map((source) => source.category))];
    const articles = await rssService.fetchAll(config.sources, config.perSourceLimit);

    logger.info("RSS fetch completed", {
        articleCount: articles.length,
    });

    let publishedCount = 0;
    let skippedCount = 0;
    let failedCount = 0;

    for (const article of articles) {
        if (dedupeService.isDuplicate(article)) {
            skippedCount += 1;
            logger.info("Skipping duplicate article", {
                title: article.title,
                link: article.link,
            });
            continue;
        }

        try {
            const rewritten = transformArticle(article, categories);
            const image = await resolveArticleImage(imageService, article, logger);
            const payload = {
                title: rewritten.title,
                content: rewritten.content,
                summary: rewritten.summary,
                slug: rewritten.slug,
                category: rewritten.category,
                thumbnail: image.thumbnail,
                thumbnail_caption: image.thumbnailCaption,
                source_url: article.link,
                published_at: article.pubDate,
            };

            if (config.dryRun) {
                logger.info("Dry run article prepared", payload);
                dedupeService.remember(article);
                publishedCount += 1;
                continue;
            }

            const response = await apiService.publishNews(payload);

            dedupeService.remember(article);

            if (response?.duplicate) {
                skippedCount += 1;
            } else {
                publishedCount += 1;
            }
        } catch (error) {
            failedCount += 1;
            logger.error("Article processing failed", {
                title: article.title,
                error: error.message,
                stack: error.stack,
            });
        }
    }

    await dedupeService.persist();

    logger.info("News bot finished", {
        publishedCount,
        skippedCount,
        failedCount,
    });
}

function getConfig() {
    const sources = parseSources(process.env.NEWS_BOT_SOURCES);

    return {
        sources,
        dryRun: process.env.NEWS_BOT_DRY_RUN === "true",
        laravelApiUrl: process.env.LARAVEL_API_URL,
        laravelApiToken: process.env.LARAVEL_API_TOKEN || "",
        statePath: process.env.NEWS_BOT_STATE_PATH || DEFAULT_STATE_PATH,
        logPath: process.env.NEWS_BOT_LOG_PATH || DEFAULT_LOG_PATH,
        timeoutMs: parseNumber(process.env.NEWS_BOT_TIMEOUT_MS, DEFAULT_TIMEOUT_MS),
        perSourceLimit: parseNumber(
            process.env.NEWS_BOT_PER_SOURCE_LIMIT,
            DEFAULT_PER_SOURCE_LIMIT,
        ),
        maxContentChars: parseNumber(
            process.env.NEWS_BOT_MAX_CONTENT_CHARS,
            DEFAULT_MAX_CONTENT_CHARS,
        ),
        minContentChars: parseNumber(
            process.env.NEWS_BOT_MIN_CONTENT_CHARS,
            DEFAULT_MIN_CONTENT_CHARS,
        ),
    };
}

function transformArticle(article, categories) {
    const title = normalizeTitle(article.title);
    const content = normalizeContent(article.content, title);
    const summary = buildSummary(content, title);
    const category = categories.includes(article.category) ? article.category : categories[0];

    return {
        title,
        content,
        summary,
        slug: makeSlug(title),
        category,
    };
}

async function resolveArticleImage(imageService, article, logger) {
    try {
        return await imageService.resolveAndStore(article);
    } catch (error) {
        logger?.error("Image processing failed, continuing without thumbnail", {
            title: article.title,
            link: article.link,
            error: error.message,
        });

        return {
            thumbnail: null,
            thumbnailCaption: null,
        };
    }
}

function parseSources(rawValue) {
    if (!rawValue) {
        throw new Error(
            'NEWS_BOT_SOURCES is required. Example: [{"url":"https://example.com/rss","category":"gundem"}]',
        );
    }

    let parsed;

    try {
        parsed = JSON.parse(rawValue);
    } catch (error) {
        throw new Error(`NEWS_BOT_SOURCES must be valid JSON: ${error.message}`);
    }

    if (!Array.isArray(parsed) || parsed.length === 0) {
        throw new Error("NEWS_BOT_SOURCES must be a non-empty array.");
    }

    return parsed.map((source, index) => {
        const url = String(source?.url || "").trim();
        const category = String(source?.category || "").trim();

        if (!url || !category) {
            throw new Error(`Source at index ${index} must include url and category.`);
        }

        return { url, category };
    });
}

function normalizeTitle(title) {
    return String(title || "")
        .replace(/\s+/g, " ")
        .trim()
        .slice(0, 255);
}

function normalizeContent(content, title) {
    const cleaned = String(content || "")
        .replace(/\s+/g, " ")
        .trim();

    if (cleaned.length >= DEFAULT_MIN_CONTENT_CHARS) {
        return cleaned;
    }

    return [
        title,
        cleaned,
        "Kaynak akışındaki içerik sınırlı olduğu için metin temel ayrıntılar korunarak yayına hazırlanmıştır.",
    ]
        .filter(Boolean)
        .join(" ");
}

function buildSummary(content, title) {
    const candidate = content.slice(0, 260).trim();

    if (candidate.length >= 40) {
        return candidate;
    }

    return title;
}

function makeSlug(value) {
    return String(value || "")
        .toLocaleLowerCase("tr-TR")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "")
        .replace(/-{2,}/g, "-")
        .slice(0, 255);
}

function parseNumber(value, fallback) {
    const parsed = Number(value);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
}

function createLogger(logPath) {
    const resolvedPath = path.resolve(logPath);

    function write(level, message, meta = {}) {
        const payload = {
            timestamp: new Date().toISOString(),
            level,
            message,
            meta,
        };

        const line = `${JSON.stringify(payload)}\n`;
        const printer = level === "error" ? console.error : console.log;
        printer(`[${payload.timestamp}] ${level.toUpperCase()} ${message}`, meta);

        try {
            fs.mkdirSync(path.dirname(resolvedPath), { recursive: true });
            fs.appendFileSync(resolvedPath, line, "utf8");
        } catch (error) {
            console.error("Failed to write bot log", error.message);
        }
    }

    return {
        info(message, meta) {
            return write("info", message, meta);
        },
        error(message, meta) {
            return write("error", message, meta);
        },
    };
}

main().catch((error) => {
    console.error("[news-bot:fatal]", error);
    process.exitCode = 1;
});
