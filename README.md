<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## News Bot

This project includes a modular Node.js news bot under `scripts/news-bot/`.

### Features

- Pulls articles from multiple RSS sources with `rss-parser`
- Maps each RSS source to a category
- Produces local title, summary, slug, and normalized content without an AI dependency
- Expands short source content with a safe fallback note and truncates very long inputs
- Downloads article images from RSS fields or `og:image` and saves them into Laravel storage
- Prevents duplicates using hashed source links and titles
- Sends finished articles to the Laravel API with `axios`
- Writes structured logs to `storage/logs/news-bot.log`

### Setup

1. Install Node dependencies:

   ```bash
   npm install
   ```

2. Copy the new bot variables from `.env.example` into your `.env`.

3. Set your RSS sources in `NEWS_BOT_SOURCES` as JSON:

   ```json
   [
     { "url": "https://example.com/gundem/rss", "category": "gundem" },
     { "url": "https://example.com/spor/rss", "category": "spor" }
   ]
   ```

4. Set `LARAVEL_API_URL`.

   If you want to protect the ingest endpoint, set the same value in
   `NEWS_BOT_INGEST_TOKEN` and `LARAVEL_API_TOKEN`.

5. Run the bot:

   ```bash
   npm run news:bot
   ```

### Laravel API Endpoint

The bot now publishes to `POST /api/news`.

- The route is loaded from `routes/api.php`
- If `NEWS_BOT_INGEST_TOKEN` is empty, the endpoint stays open for local development
- If `NEWS_BOT_INGEST_TOKEN` is set, send it as a Bearer token
- If the category slug does not exist yet, Laravel creates it automatically
- Duplicate source URLs return `409 Conflict`

### Local Test Flow

1. Install PHP and Node dependencies:

   ```bash
   composer install
   npm install
   ```

2. Prepare the application:

   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

3. Start Laravel:

   ```bash
   php artisan serve
   ```

4. Test the ingest endpoint directly:

   ```bash
   curl -X POST http://127.0.0.1:8000/api/news \
     -H "Content-Type: application/json" \
     -d '{
       "title":"Test Haber",
       "content":"<p>Ornek haber icerigi.</p>",
       "summary":"Ornek ozet.",
       "slug":"test-haber",
       "category":"gundem",
       "source_url":"https://example.com/test-haber",
       "published_at":"2026-05-06T12:00:00Z"
     }'
   ```

5. Then run the bot:

   ```bash
   npm run news:bot
   ```

### Files

- `scripts/news-bot/rssService.js`
- `scripts/news-bot/dedupeService.js`
- `scripts/news-bot/apiService.js`
- `scripts/news-bot/bot.js`

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
