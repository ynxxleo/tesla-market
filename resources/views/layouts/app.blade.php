<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tesla Markets')</title>
    <script>if (localStorage.theme === 'light') document.documentElement.classList.add('light')</script>
    @vite(['resources/css/app.css', 'resources/css/premium.css', 'resources/css/trading.css', 'resources/css/operations.css', 'resources/css/experience.css', 'resources/css/mobile-profile.css', 'resources/css/mobile-overhaul.css', 'resources/css/mobile-menu.css', 'resources/css/theme-legal.css', 'resources/css/news.css', 'resources/js/app.js'])
</head>
<body>
    <div class="preloader" aria-hidden="true"><div class="preloader-mark">T</div><div class="preloader-line"><i></i></div><span>INITIALIZING MARKETS</span></div>
    <div class="ambient"></div>
    <header class="nav">
        <a class="logo" href="{{ route('home') }}">TESLA</a>
        @auth
            <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="primary-navigation"><i></i><i></i><i></i></button>
            <nav id="primary-navigation">
                <a href="{{ route('dashboard') }}">Overview</a>
                <a href="{{ route('trade.index') }}">Trade</a>
                <a href="{{ route('news') }}">News</a>
                <a href="{{ route('investments.index') }}">Invest</a>
                <a href="{{ route('funding.index') }}">Funding</a>
                <a href="{{ route('kyc.show') }}">Verify</a>
                <a href="{{ route('assistant.index') }}">Grok Assistant</a>
                <a href="{{ route('profile.show') }}">Profile</a>
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                    <a href="{{ route('admin.compliance') }}">Compliance</a>
                @endif
            </nav>
            <div class="actions">
                <span class="balance">SIM ${{ number_format(auth()->user()->cash_balance, 2) }}</span>
                <button class="theme" type="button">☼</button>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="quiet">Log out</button>
                </form>
            </div>
        @else
            <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="guest-navigation"><i></i><i></i><i></i></button>
            <nav id="guest-navigation" class="guest-mobile-nav"><a href="{{ route('home') }}">Home</a><a href="{{ route('news') }}">News</a><a href="{{ route('about') }}">About</a><a href="{{ route('fees') }}">Fees</a><a href="{{ route('help') }}">Help</a><a href="{{ route('contact') }}">Contact</a></nav>
            <div class="actions">
                <button class="theme" type="button">☼</button>
                <a href="{{ route('login') }}">Log in</a>
                <a class="button" href="{{ route('register') }}">Create account</a>
            </div>
        @endauth
    </header>
    <main class="container">
        @if(session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
    <footer class="site-footer"><div class="footer-brand"><a class="logo" href="{{ route('home') }}">TESLA</a><p>A premium platform for learning stocks, crypto, portfolio construction, and disciplined risk.</p><span>Educational simulation only. No live custody or guaranteed returns.</span><div class="social-links"><a href="https://x.com" target="_blank" rel="noopener" aria-label="X">𝕏</a><a href="https://www.linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn">in</a><a href="https://www.instagram.com" target="_blank" rel="noopener" aria-label="Instagram">◎</a><a href="https://www.youtube.com" target="_blank" rel="noopener" aria-label="YouTube">▶</a></div></div><div><b>PLATFORM</b><a href="{{ route('about') }}">About</a><a href="{{ route('fees') }}">Fees</a><a href="{{ route('help') }}">Help center</a><a href="{{ route('contact') }}">Contact us</a></div><div><b>LEGAL</b><a href="{{ route('terms') }}">Terms of service</a><a href="{{ route('privacy') }}">Privacy policy</a><a href="{{ route('legal') }}">Legal</a><a href="{{ route('risk') }}">Risk disclosure</a></div><div><b>COMPLIANCE</b><a href="{{ route('aml') }}">AML policy</a><a href="{{ route('kyc.show') }}">Identity verification</a><a href="{{ route('contact') }}">Report a concern</a></div><small>© {{ date('Y') }} Tesla Markets Simulator. Independent educational prototype; not affiliated with Tesla, Inc.</small></footer>
    <a class="footer-facebook" href="https://www.facebook.com" target="_blank" rel="noopener" aria-label="Facebook">f <span>Facebook</span></a>
</body>
</html>
