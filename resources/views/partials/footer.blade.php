<footer class="site-footer">
    <div class="footer-top">
        <div class="footer-brand">
            <div class="footer-logo">Gündem<span>TR</span></div>
            <p>Türkiye'nin güvenilir haber kaynağı. Doğru, hızlı ve tarafsız habercilik anlayışıyla 2024'ten bu yana yayındayız.</p>
        </div>

        <div class="footer-col">
            <h4>Kategoriler</h4>
            <ul>
                @foreach($navCategories as $slug => $label)
                    <li><a href="{{ route('news.index', ['category' => $slug]) }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="footer-col">
            <h4>Kurumsal</h4>
            <ul>
                <li><a href="#">Hakkımızda</a></li>
                <li><a href="#">Künye</a></li>
                <li><a href="#">İletişim</a></li>
                <li><a href="#">Reklam</a></li>
                <li><a href="#">Gizlilik Politikası</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Takip Edin</h4>
            <ul>
                <li><a href="#" target="_blank">Twitter / X</a></li>
                <li><a href="#" target="_blank">Instagram</a></li>
                <li><a href="#" target="_blank">YouTube</a></li>
                <li><a href="#" target="_blank">Telegram</a></li>
                <li><a href="#">RSS</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        © {{ date('Y') }} GündemTR. Tüm hakları saklıdır.
    </div>
</footer>   