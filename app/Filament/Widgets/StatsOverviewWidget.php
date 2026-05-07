<?php

namespace App\Filament\Widgets;

use App\Models\News;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $mostRead = News::orderByDesc('view_count')->first();

        return [
            Stat::make('Toplam Haber', News::count())
                ->description('Yayındaki ve taslak haberler')
                ->descriptionIcon('heroicon-m-document-text'),
            
            Stat::make('Bugün Yayınlanan', News::whereDate('published_at', today())->count())
                ->description('Bugün eklenen haberler'),

            Stat::make('Toplam Görüntülenme', News::sum('view_count'))
                ->description('Tüm zamanlar')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Toplam Yazar', User::whereIn('role', ['admin', 'editör', 'yazar'])->count())
                ->description('Sistemdeki kullanıcılar'),

            Stat::make('Son Dakika Haberleri', News::where('is_breaking', true)->count())
                ->color('danger'),

            Stat::make('En Çok Okunan', $mostRead ? \Illuminate\Support\Str::limit($mostRead->title, 30) : 'Yok')
                ->description($mostRead ? $mostRead->view_count . ' kez okundu' : '')
                ->color('info'),
        ];
    }
}
