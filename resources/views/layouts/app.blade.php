<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GündemTR') - Türkiye'nin Haber Kaynağı</title>
    <meta name="description" content="@yield('meta_description', 'GündemTR ile güncel haberleri takip edin.')">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

    @include('partials.header')
    @include('partials.navbar')
    @include('partials.ticker')

    <main class="main-content">
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>