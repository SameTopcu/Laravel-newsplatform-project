import crypto from "node:crypto";
import fs from "node:fs/promises";
import path from "node:path";

const DEFAULT_MAX_ENTRIES = 5000;

export async function createDedupeService({
    logger,
    stateFilePath,
    maxEntries = DEFAULT_MAX_ENTRIES,
}) {
    const resolvedPath = path.resolve(stateFilePath);
    const state = await loadState(resolvedPath, logger);

    const linkHashes = new Set(state.linkHashes);
    const titleHashes = new Set(state.titleHashes);

    function isDuplicate(article) {
        const { linkHash, titleHash } = buildHashes(article);

        return linkHashes.has(linkHash) || titleHashes.has(titleHash);
    }

    function remember(article) {
        const { linkHash, titleHash } = buildHashes(article);

        linkHashes.add(linkHash);
        titleHashes.add(titleHash);
    }

    async function persist() {
        await fs.mkdir(path.dirname(resolvedPath), { recursive: true });

        const payload = JSON.stringify(
            {
                linkHashes: trimEntries(linkHashes, maxEntries),
                titleHashes: trimEntries(titleHashes, maxEntries),
            },
            null,
            2,
        );

        await fs.writeFile(resolvedPath, payload, "utf8");
    }

    return {
        isDuplicate,
        remember,
        persist,
    };
}

async function loadState(filePath, logger) {
    try {
        const content = await fs.readFile(filePath, "utf8");
        const parsed = JSON.parse(content);

        return {
            linkHashes: Array.isArray(parsed.linkHashes) ? parsed.linkHashes : [],
            titleHashes: Array.isArray(parsed.titleHashes) ? parsed.titleHashes : [],
        };
    } catch (error) {
        if (error.code !== "ENOENT") {
            logger?.error("Unable to read dedupe state, starting fresh", {
                error: error.message,
                filePath,
            });
        }

        return {
            linkHashes: [],
            titleHashes: [],
        };
    }
}

function buildHashes(article) {
    return {
        linkHash: makeHash(normalize(article.link || article.source_url || "")),
        titleHash: makeHash(normalize(article.title || "")),
    };
}

function makeHash(value) {
    return crypto.createHash("sha256").update(value).digest("hex");
}

function normalize(value) {
    return String(value || "")
        .toLocaleLowerCase("tr-TR")
        .replace(/\s+/g, " ")
        .trim();
}

function trimEntries(set, maxEntries) {
    const values = Array.from(set);

    if (values.length <= maxEntries) {
        return values;
    }

    return values.slice(values.length - maxEntries);
}
