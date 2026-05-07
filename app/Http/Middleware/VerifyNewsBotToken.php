<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyNewsBotToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('app.news_bot_ingest_token', env('NEWS_BOT_INGEST_TOKEN', ''));

        if ($expectedToken === '') {
            return $next($request);
        }

        $providedToken = $request->bearerToken() ?: (string) $request->header('X-News-Bot-Token', '');

        if ($providedToken !== '' && hash_equals($expectedToken, $providedToken)) {
            return $next($request);
        }

        return new JsonResponse([
            'message' => 'Unauthorized bot request.',
        ], 401);
    }
}
