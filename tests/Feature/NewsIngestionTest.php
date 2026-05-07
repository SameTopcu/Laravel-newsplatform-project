<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_news_from_the_bot_payload(): void
    {
        User::query()->create([
            'name' => 'Bot Admin',
            'email' => 'admin@gundemtr.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/news', [
            'title' => 'Ekonomi Gündeminde Yeni Gelişme',
            'content' => '<p>Uzun haber içeriği burada yer alır.</p>',
            'summary' => 'Kısa özet metni burada yer alır.',
            'slug' => 'ekonomi-gundeminde-yeni-gelisme',
            'category' => 'ekonomi',
            'source_url' => 'https://example.com/haber/1',
            'published_at' => now()->toISOString(),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'News created successfully.');

        $this->assertDatabaseHas('news', [
            'slug' => 'ekonomi-gundeminde-yeni-gelisme',
            'source_url_hash' => hash('sha256', 'https://example.com/haber/1'),
            'is_published' => true,
        ]);
    }

    public function test_it_rejects_duplicate_source_urls(): void
    {
        $user = User::query()->create([
            'name' => 'Bot Admin',
            'email' => 'admin@gundemtr.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        News::query()->create([
            'author_id' => $user->id,
            'category_id' => \App\Models\Category::query()->create([
                'name' => 'Ekonomi',
                'slug' => 'ekonomi',
            ])->id,
            'title' => 'Mevcut Haber',
            'slug' => 'mevcut-haber',
            'excerpt' => 'Ozet',
            'content' => 'Icerik',
            'source_url' => 'https://example.com/haber/1',
            'source_url_hash' => hash('sha256', 'https://example.com/haber/1'),
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->postJson('/api/news', [
            'title' => 'Yeni Baslik',
            'content' => 'Yeni icerik',
            'summary' => 'Yeni ozet',
            'slug' => 'yeni-baslik',
            'category' => 'ekonomi',
            'source_url' => 'https://example.com/haber/1',
            'published_at' => now()->toISOString(),
        ]);

        $response
            ->assertStatus(409)
            ->assertJsonPath('message', 'News already imported from this source URL.');
    }

    public function test_it_requires_the_configured_bot_token_when_present(): void
    {
        config()->set('app.news_bot_ingest_token', 'secret-token');

        $response = $this->postJson('/api/news', [
            'title' => 'Token Test Haber',
            'content' => 'Icerik',
            'summary' => 'Ozet',
            'slug' => 'token-test-haber',
            'category' => 'gundem',
            'source_url' => 'https://example.com/haber/2',
            'published_at' => now()->toISOString(),
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthorized bot request.');
    }
}
