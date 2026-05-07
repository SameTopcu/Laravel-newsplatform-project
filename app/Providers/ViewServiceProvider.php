<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $shared = function (): array {
            $navCategories = Category::query()
                ->orderBy('id')
                ->pluck('name', 'slug')
                ->all();

            return [
                'navCategories' => $navCategories,
                'breakingNews' => News::query()
                    ->published()
                    ->where('is_breaking', true)
                    ->latest('published_at')
                    ->take(10)
                    ->get(),
                'sidebarBreaking' => News::query()
                    ->published()
                    ->latest('published_at')
                    ->take(5)
                    ->get(),
                'mostRead' => News::query()
                    ->published()
                    ->orderByDesc('view_count')
                    ->take(4)
                    ->get(),
            ];
        };

        View::composer(
            [
                'partials.navbar',
                'partials.ticker',
                'partials.sidebar',
                'partials.footer',
                'news.index',
                'news.show',
            ],
            fn ($view) => $view->with($shared())
        );
    }
}
