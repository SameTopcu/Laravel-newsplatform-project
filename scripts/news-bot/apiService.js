import axios from "axios";

const DEFAULT_TIMEOUT_MS = 15000;

export function createApiService({
    logger,
    baseUrl,
    token,
    timeoutMs = DEFAULT_TIMEOUT_MS,
}) {
    if (!baseUrl) {
        throw new Error("LARAVEL_API_URL is required for the news bot.");
    }

    const client = axios.create({
        baseURL: baseUrl,
        timeout: timeoutMs,
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
    });

    async function publishNews(payload) {
        logger?.info("Publishing article to Laravel API", {
            title: payload.title,
            category: payload.category,
        });

        try {
            const response = await client.post("/api/news", payload);
            return response.data;
        } catch (error) {
            if (error.response?.status === 409) {
                logger?.info("Laravel API reported duplicate article", {
                    title: payload.title,
                    sourceUrl: payload.source_url,
                });

                return {
                    duplicate: true,
                };
            }

            throw error;
        }
    }

    return {
        publishNews,
    };
}
