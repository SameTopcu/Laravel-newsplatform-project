<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsIngestionController extends Controller
{
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $sourceUrlHash = hash('sha256', $payload['source_url']);

        if (News::query()->where('source_url_hash', $sourceUrlHash)->exists()) {
            return response()->json([
                'message' => 'News already imported from this source URL.',
            ], 409);
        }

        if (News::query()->where('slug', $payload['slug'])->exists()) {
            return response()->json([
                'message' => 'News slug already exists.',
            ], 409);
        }

        $author = $this->resolveAuthor();

        if (! $author) {
            return response()->json([
                'message' => 'No author user found. Seed users first or set NEWS_BOT_AUTHOR_EMAIL.',
            ], 422);
        }

        $category = Category::query()->firstOrCreate(
            ['slug' => $payload['category']],
            ['name' => Str::headline(str_replace('-', ' ', $payload['category']))]
        );

        $news = DB::transaction(function () use ($author, $category, $payload, $sourceUrlHash) {
            return News::query()->create([
                'author_id' => $author->id,
                'category_id' => $category->id,
                'title' => $payload['title'],
                'slug' => $payload['slug'],
                'excerpt' => $payload['summary'],
                'content' => $payload['content'],
                'thumbnail' => $payload['thumbnail'] ?? null,
                'thumbnail_caption' => $payload['thumbnail_caption'] ?? null,
                'source_url' => $payload['source_url'],
                'source_url_hash' => $sourceUrlHash,
                'is_published' => true,
                'is_breaking' => false,
                'view_count' => 0,
                'published_at' => $payload['published_at'],
            ]);
        });

        return response()->json([
            'message' => 'News created successfully.',
            'news_id' => $news->id,
            'slug' => $news->slug,
        ], 201);
    }

    private function resolveAuthor(): ?User
    {
        $preferredEmail = (string) env('NEWS_BOT_AUTHOR_EMAIL', '');

        if ($preferredEmail !== '') {
            $preferredAuthor = User::query()->where('email', $preferredEmail)->first();

            if ($preferredAuthor) {
                return $preferredAuthor;
            }
        }

        return User::query()->oldest('id')->first();
    }
}
