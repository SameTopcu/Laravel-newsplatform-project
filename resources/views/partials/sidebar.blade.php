<aside class="sidebar">

    <div class="sidebar-widget">
        <div class="section-title breaking-widget-title">Son Dakika</div>
        <ul class="breaking-list">
            @foreach($sidebarBreaking as $index => $item)
            <li>
                <span class="breaking-num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <div>
                    <a href="{{ route('news.show', $item->slug) }}" class="breaking-text">{{ $item->title }}</a>
                    <div class="breaking-time">{{ $item->published_at->diffForHumans() }}</div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="weather-widget">
        <div class="section-title weather-widget-title">Hava Durumu</div>
        <div class="weather-city">İstanbul, Türkiye</div>
        <div class="weather-main">
            <div>
                <div class="weather-temp">22<sup>°C</sup></div>
                <div class="weather-desc">Güneşli</div>
            </div>
            <div class="weather-icon" aria-hidden="true">☀️</div>
        </div>
        <div class="weather-details">
            <div class="weather-detail"><strong>65%</strong>Nem</div>
            <div class="weather-detail"><strong>12 km/s</strong>Rüzgar</div>
            <div class="weather-detail"><strong>18°C</strong>Hissedilen</div>
            <div class="weather-detail"><strong>26°C</strong>Maks.</div>
        </div>
        <div class="weather-days">
            <div class="weather-day"><div class="day-name">Cmt</div><div class="day-icon">⛅</div><div class="day-temp">20°</div></div>
            <div class="weather-day"><div class="day-name">Paz</div><div class="day-icon">🌧</div><div class="day-temp">17°</div></div>
            <div class="weather-day"><div class="day-name">Pzt</div><div class="day-icon">⛅</div><div class="day-temp">19°</div></div>
            <div class="weather-day"><div class="day-name">Sal</div><div class="day-icon">☀️</div><div class="day-temp">23°</div></div>
            <div class="weather-day"><div class="day-name">Çar</div><div class="day-icon">☀️</div><div class="day-temp">25°</div></div>
        </div>
    </div>

    <div class="sidebar-widget">
        <div class="section-title">En Çok Okunanlar</div>
        <ul class="breaking-list">
            @foreach($mostRead as $index => $item)
            <li>
                <span class="breaking-num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <div>
                    <a href="{{ route('news.show', $item->slug) }}" class="breaking-text">{{ $item->title }}</a>
                    <div class="breaking-time">{{ number_format($item->view_count) }} okuma</div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

</aside>
