<nav class="main-nav">
    <div class="nav-container">
        <a href="{{ route('news.index') }}"
           class="nav-link {{ request()->routeIs('news.index') && ! request('category') ? 'active' : '' }}">
            Tümü
        </a>

        @foreach($navCategories as $slug => $label)
            <a href="{{ route('news.index', ['category' => $slug]) }}"
               class="nav-link {{ request('category') === $slug ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</nav>
