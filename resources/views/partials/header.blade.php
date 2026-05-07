<header class="site-header">
    <div class="top-bar">
        <span id="current-datetime">
            {{ now()->locale('tr')->isoFormat('dddd, D MMMM YYYY') }}
        </span>
        <div class="city-weather">
            <span>İstanbul: 22°C ☀️</span>
            <span>Ankara: 18°C ⛅</span>
            <span>İzmir: 26°C ☀️</span>
        </div>
    </div>

    <div class="logo-bar">
        <a href="{{ route('news.index') }}" class="logo-link">
            <h1 class="logo">Gündem<span class="logo-accent">TR</span></h1>
        </a>
        <p class="logo-tagline">Türkiye'nin Haber Kaynağı</p>
    </div>
</header>