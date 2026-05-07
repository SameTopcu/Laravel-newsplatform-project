@if($breakingNews->isNotEmpty())
<div class="ticker" role="region" aria-label="Son dakika">
    <div class="ticker-inner">
        @foreach($breakingNews as $item)
            <a href="{{ route('news.show', $item->slug) }}" class="ticker-item">{{ $item->title }}</a>
        @endforeach
        @foreach($breakingNews as $item)
            <a href="{{ route('news.show', $item->slug) }}" class="ticker-item">{{ $item->title }}</a>
        @endforeach
    </div>
</div>
@endif
