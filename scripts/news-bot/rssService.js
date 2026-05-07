import Parser from "rss-parser";

const DEFAULT_MAX_CONTENT_CHARS = 12000;
const DEFAULT_MIN_CONTENT_CHARS = 280;
const DEFAULT_TIMEOUT_MS = 15000;

export function createRssService({
    logger,
    maxContentChars = DEFAULT_MAX_CONTENT_CHARS,
    minContentChars = DEFAULT_MIN_CONTENT_CHARS,
    timeoutMs = DEFAULT_TIMEOUT_MS,
} = {}) {
    const parser = new Parser({
        timeout: timeoutMs,
        headers: {
            "User-Agent": "ProjectNewsBot/1.0",
        },
        customFields: {
            item: [
                ["content:encoded", "contentEncoded"],
                ["content:encodedSnippet", "contentEncodedSnippet"],
                ["description", "description"],
                ["media:content", "mediaContent"],
                ["media:thumbnail", "mediaThumbnail"],
            ],
        },
    });

    async function fetchAll(sources, perSourceLimit = 10) {
        const results = await Promise.allSettled(
            sources.map((source) => fetchSource(source, perSourceLimit)),
        );

        return results.flatMap((result) => {
            if (result.status === "fulfilled") {
                return result.value;
            }

            logger?.error("RSS source failed", {
                error: result.reason?.message ?? String(result.reason),
            });

            return [];
        });
    }

    async function fetchSource(source, perSourceLimit) {
        logger?.info("Fetching RSS source", {
            url: source.url,
            category: source.category,
        });

        const feed = await parser.parseURL(source.url);
        const items = Array.isArray(feed.items) ? feed.items : [];

        return items
            .slice(0, perSourceLimit)
            .map((item) => normalizeItem(item, source))
            .filter(Boolean);
    }

    function normalizeItem(item, source) {
        const title = normalizeWhitespace(item.title);
        const link = normalizeWhitespace(item.link || item.guid || item.id);
        const rawContent = getRawContent(item);
        const content = normalizeContent(rawContent);
        const publishedAt = normalizePublishedAt(item);
        const imageCandidates = getImageCandidates(item, rawContent);

        if (!title || !link) {
            return null;
        }

        return {
            title,
            link,
            content,
            pubDate: publishedAt,
            category: source.category,
            sourceUrl: source.url,
            imageCandidates,
            isShortContent: content.length < minContentChars,
            wasTruncated: rawContent.length > maxContentChars,
        };
    }

    function normalizeContent(rawContent) {
        const cleanText = stripHtml(rawContent);

        if (cleanText.length <= maxContentChars) {
            return cleanText;
        }

        return cleanText.slice(0, maxContentChars).trim();
    }

    return {
        fetchAll,
    };
}

function getRawContent(item) {
    const preferredFields = [
        item.contentEncoded,
        item["content:encoded"],
        item.content,
        item.contentSnippet,
        item.contentEncodedSnippet,
        item.description,
        item.summary,
    ];

    for (const value of preferredFields) {
        if (typeof value === "string" && value.trim()) {
            return value;
        }
    }

    return "";
}

function normalizePublishedAt(item) {
    const candidate = item.isoDate || item.pubDate || item.published || item.updated;
    const timestamp = candidate ? Date.parse(candidate) : NaN;

    if (Number.isNaN(timestamp)) {
        return new Date().toISOString();
    }

    return new Date(timestamp).toISOString();
}

function getImageCandidates(item, rawContent) {
    const candidates = [
        item.enclosure?.url,
        item.image?.url,
        item.mediaContent?.url,
        item.mediaThumbnail?.url,
        extractFirstImageFromHtml(rawContent),
    ];

    return [...new Set(candidates.filter(Boolean).map(normalizeWhitespace))];
}

function normalizeWhitespace(value) {
    if (typeof value !== "string") {
        return "";
    }

    return value.replace(/\s+/g, " ").trim();
}

function stripHtml(value) {
    if (typeof value !== "string") {
        return "";
    }

    return normalizeWhitespace(
        value
            .replace(/<script[\s\S]*?<\/script>/gi, " ")
            .replace(/<style[\s\S]*?<\/style>/gi, " ")
            .replace(/<[^>]+>/g, " ")
            .replace(/&nbsp;/gi, " ")
            .replace(/&amp;/gi, "&")
            .replace(/&quot;/gi, '"')
            .replace(/&#39;/gi, "'"),
    );
}

function extractFirstImageFromHtml(value) {
    if (typeof value !== "string") {
        return "";
    }

    const match = value.match(/<img[^>]+src=["']([^"']+)["']/i);
    return match?.[1] ?? "";
}
