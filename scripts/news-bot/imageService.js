import axios from "axios";
import crypto from "node:crypto";
import fs from "node:fs/promises";
import path from "node:path";

const DEFAULT_TIMEOUT_MS = 15000;
const IMAGE_MIME_EXTENSIONS = {
    "image/jpeg": "jpg",
    "image/png": "png",
    "image/webp": "webp",
    "image/gif": "gif",
};

export function createImageService({
    logger,
    timeoutMs = DEFAULT_TIMEOUT_MS,
    storageRoot = "storage/app/public/news-bot",
    publicPrefix = "/storage/news-bot",
} = {}) {
    const http = axios.create({
        timeout: timeoutMs,
        headers: {
            "User-Agent": "ProjectNewsBot/1.0",
        },
        maxRedirects: 5,
    });

    async function resolveAndStore(article) {
        const imageUrl = await findImageUrl(article);

        if (!imageUrl) {
            return {
                thumbnail: null,
                thumbnailCaption: null,
            };
        }

        const response = await http.get(imageUrl, {
            responseType: "arraybuffer",
        });

        const mimeType = String(response.headers["content-type"] || "").split(";")[0].trim();

        if (!mimeType.startsWith("image/")) {
            logger?.info("Skipping non-image thumbnail candidate", {
                imageUrl,
                mimeType,
            });

            return {
                thumbnail: null,
                thumbnailCaption: null,
            };
        }

        const extension = getExtension(imageUrl, mimeType);
        const directory = buildStorageDirectory(storageRoot, publicPrefix);
        const filename = `${makeSlug(article.title)}-${crypto.randomBytes(4).toString("hex")}.${extension}`;
        const absolutePath = path.join(directory.absolute, filename);

        await fs.mkdir(directory.absolute, { recursive: true });
        await fs.writeFile(absolutePath, response.data);

        return {
            thumbnail: `${directory.public}/${filename}`,
            thumbnailCaption: article.title,
        };
    }

    async function findImageUrl(article) {
        for (const candidate of article.imageCandidates || []) {
            if (isHttpUrl(candidate)) {
                return candidate;
            }
        }

        try {
            const response = await http.get(article.link, {
                responseType: "text",
            });

            const html = String(response.data || "");
            return extractMetaImage(html);
        } catch (error) {
            logger?.error("Unable to inspect article page for image", {
                link: article.link,
                error: error.message,
            });

            return null;
        }
    }

    return {
        resolveAndStore,
    };
}

function extractMetaImage(html) {
    const patterns = [
        /<meta[^>]+property=["']og:image["'][^>]+content=["']([^"']+)["']/i,
        /<meta[^>]+content=["']([^"']+)["'][^>]+property=["']og:image["']/i,
        /<meta[^>]+name=["']twitter:image["'][^>]+content=["']([^"']+)["']/i,
        /<img[^>]+src=["']([^"']+)["']/i,
    ];

    for (const pattern of patterns) {
        const match = html.match(pattern);
        const candidate = match?.[1]?.trim();

        if (candidate && isHttpUrl(candidate)) {
            return candidate;
        }
    }

    return null;
}

function buildStorageDirectory(storageRoot, publicPrefix) {
    const now = new Date();
    const year = String(now.getUTCFullYear());
    const month = String(now.getUTCMonth() + 1).padStart(2, "0");

    return {
        absolute: path.resolve(storageRoot, year, month),
        public: `${publicPrefix}/${year}/${month}`,
    };
}

function getExtension(imageUrl, mimeType) {
    const fromMimeType = IMAGE_MIME_EXTENSIONS[mimeType];

    if (fromMimeType) {
        return fromMimeType;
    }

    const pathname = new URL(imageUrl).pathname;
    const extension = path.extname(pathname).replace(".", "").toLowerCase();

    if (extension) {
        return extension;
    }

    return "jpg";
}

function makeSlug(value) {
    return String(value || "")
        .toLocaleLowerCase("en-US")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "")
        .replace(/-{2,}/g, "-")
        .slice(0, 80) || "news-image";
}

function isHttpUrl(value) {
    try {
        const url = new URL(String(value || ""));
        return url.protocol === "http:" || url.protocol === "https:";
    } catch {
        return false;
    }
}
