@extends('layouts.app')

@section('title', $news->title)
@section('meta_description', $news->excerpt)

@section('content')
<div class="container">
    <div class="main-grid">

        <article class="article">

            <nav class="breadcrumb" aria-label="Sayfa konumu">
                <a href="{{ route('news.index') }}">Ana Sayfa</a>
                <span>/</span>
                <a href="{{ route('news.index', ['category' => $news->category->slug]) }}">
                    {{ $news->category->name }}
                </a>
                <span>/</span>
                <span>{{ Str::limit($news->title, 48) }}</span>
            </nav>

            <div class="article-cat">{{ $news->category->name }}</div>
            <h1 class="article-title">{{ $news->title }}</h1>
            <p class="article-excerpt">{{ $news->excerpt }}</p>

            <div class="article-meta">
                <div class="author-info">
                    <div class="author-avatar" aria-hidden="true">
                        {{ strtoupper(mb_substr($news->author->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="author-name">{{ $news->author->name }}</div>
                        <div class="article-date">
                            {{ $news->published_at->locale('tr')->isoFormat('D MMMM YYYY, HH:mm') }}
                            · {{ $news->read_time }} dk okuma
                        </div>
                    </div>
                </div>
                <div class="share-buttons">
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($news->title) }}"
                       target="_blank" rel="noopener noreferrer" class="share-btn share-twitter">Twitter</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                       target="_blank" rel="noopener noreferrer" class="share-btn share-facebook">Facebook</a>
                    <button type="button" class="share-btn share-copy" data-copy-url="{{ request()->url() }}">Linki Kopyala</button>
                </div>
            </div>

            @if($news->thumbnail)
            <figure class="article-figure">
                <img src="{{ asset('storage/'.$news->thumbnail) }}"
                     alt="{{ $news->title }}"
                     class="article-img"
                     loading="eager">
                @if($news->thumbnail_caption)
                    <figcaption>{{ $news->thumbnail_caption }}</figcaption>
                @endif
            </figure>
            @endif

            <div class="article-body">
                {!! $news->content !!}
            </div>

            @if($news->tags->isNotEmpty())
            <div class="article-tags">
                <span class="tags-label">Etiketler:</span>
                @foreach($news->tags as $tag)
                    <a href="{{ route('news.index', ['tag' => $tag->slug]) }}" class="tag-pill">{{ $tag->name }}</a>
                @endforeach
            </div>
            @endif

        </article>

        @include('partials.sidebar')
    </div>

    @if($relatedNews->isNotEmpty())
    <section class="related-news">
        <div class="section-title">İlgili Haberler</div>
        <div class="news-grid">
            @foreach($relatedNews as $related)
                <a href="{{ route('news.show', $related->slug) }}" class="news-card">
                    <div
                        @class([
                            'news-card-img',
                            'cat-' . $related->category->slug,
                            'news-card-has-thumb' => (bool) $related->thumbnail,
                        ])
                        @if($related->thumbnail)
                            style="background-image: url('{{ e(asset('storage/'.$related->thumbnail)) }}')"
                        @endif
                    >
                        <span class="cat-badge {{ $related->category->slug }}">{{ $related->category->name }}</span>
                    </div>
                    <div class="news-card-body">
                        <h3>{{ $related->title }}</h3>
                        <p>{{ Str::limit($related->excerpt, 100) }}</p>
                        <div class="news-meta">{{ $related->published_at->diffForHumans() }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection

@push('scripts')
<script>
    fetch(@json(route('news.increment-view', $news->id)), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    });
    document.querySelectorAll('.share-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-copy-url');
            if (navigator.clipboard && url) {
                navigator.clipboard.writeText(url);
            }
        });
    });
</script>
@endpush
