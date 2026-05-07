@extends('layouts.app')

@section('title', 'Ana Sayfa')

@section('content')
<div class="container">

    @if($heroNews)
    <section class="hero">
        <div class="section-title">
            Manşet
            <span>Son güncelleme: {{ $heroNews->published_at?->format('H:i') }}</span>
        </div>
        <a href="{{ route('news.show', $heroNews->slug) }}" class="hero-card">
            <div
                @class([
                    'hero-img',
                    'cat-' . $heroNews->category->slug,
                    'hero-img-has-thumb' => (bool) $heroNews->thumbnail,
                ])
                @if($heroNews->thumbnail)
                    style="background-image: url('{{ e(asset('storage/'.$heroNews->thumbnail)) }}')"
                @endif
            >
                @if($heroNews->is_breaking)
                    <span class="hero-img-label">Son Dakika</span>
                @endif
            </div>
            <div class="hero-content">
                <div class="hero-cat">{{ $heroNews->category->name }}</div>
                <h2>{{ $heroNews->title }}</h2>
                <p>{{ $heroNews->excerpt }}</p>
                <div class="hero-meta">
                    <strong>{{ $heroNews->author->name }}</strong>
                    · {{ $heroNews->published_at?->diffForHumans() }}
                    · {{ $heroNews->read_time }} dk okuma
                </div>
            </div>
        </a>
    </section>
    @else
    <p class="empty-state">Henüz yayınlanmış haber bulunmuyor.</p>
    @endif

    <div class="main-grid">
        <div class="main-col">
            <div class="section-title">
                Son Haberler
                <span><a href="{{ route('news.index') }}" class="section-title-link">Tümünü gör →</a></span>
            </div>

            <div class="cat-filter" role="navigation" aria-label="Kategori filtresi">
                <a href="{{ route('news.index') }}"
                   class="cat-btn {{ ! request('category') ? 'active' : '' }}">Tümü</a>
                @foreach($navCategories ?? [] as $slug => $label)
                    <a href="{{ route('news.index', ['category' => $slug]) }}"
                       class="cat-btn {{ request('category') === $slug ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <div class="news-grid">
                @forelse($newsList as $item)
                    <a href="{{ route('news.show', $item->slug) }}" class="news-card" data-cat="{{ $item->category->slug }}">
                        <div
                            @class([
                                'news-card-img',
                                'cat-' . $item->category->slug,
                                'news-card-has-thumb' => (bool) $item->thumbnail,
                            ])
                            @if($item->thumbnail)
                                style="background-image: url('{{ e(asset('storage/'.$item->thumbnail)) }}')"
                            @endif
                        >
                            <span class="cat-badge {{ $item->category->slug }}">{{ $item->category->name }}</span>
                        </div>
                        <div class="news-card-body">
                            <h3>{{ $item->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($item->excerpt, 100) }}</p>
                            <div class="news-meta">{{ $item->published_at?->diffForHumans() }}</div>
                        </div>
                    </a>
                @empty
                    <p class="no-news">Bu kriterlere uygun haber bulunamadı.</p>
                @endforelse
            </div>

            @if($newsList->hasPages())
            <div class="pagination-wrapper">
                {{ $newsList->appends(request()->query())->links() }}
            </div>
            @endif
        </div>

        @include('partials.sidebar')
    </div>
</div>
@endsection
