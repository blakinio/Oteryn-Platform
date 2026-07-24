<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    @stack('head')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brand-art.css') }}">
    @stack('styles')
</head>
<body class="public-body">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
    <div class="header-inner">
        <a class="brand portal-brand" href="{{ route('home') }}" aria-label="Oteryn Platform home">
            <img class="brand-wordmark-art" src="{{ asset('images/oteryn-wordmark.svg') }}" alt="" aria-hidden="true">
        </a>

        <nav class="primary-nav desktop-only" aria-label="Public navigation">
            <a class="nav-link" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            <a class="nav-link" href="{{ route('news.index') }}" @if(request()->routeIs('news.*')) aria-current="page" @endif>News</a>
            <a class="nav-link" href="{{ route('game.online.index') }}" @if(request()->routeIs('game.online.*')) aria-current="page" @endif>Online</a>
            <a class="nav-link" href="{{ route('game.highscores.index') }}" @if(request()->routeIs('game.highscores.*')) aria-current="page" @endif>Highscores</a>
            <a class="nav-link" href="{{ route('game.servers.index') }}" @if(request()->routeIs('game.servers.*')) aria-current="page" @endif>Servers</a>
        </nav>

        <div class="account-actions desktop-only">
            @guest
                <a class="nav-link" href="{{ route('identity.login.create') }}" @if(request()->routeIs('identity.login.*')) aria-current="page" @endif>Sign in</a>
                <a class="button" href="{{ route('identity.register.create') }}">Create account</a>
            @else
                <a class="button button-secondary" href="{{ route('account.overview') }}">Account</a>
                <form method="POST" action="{{ route('identity.logout') }}">
                    @csrf
                    <button class="button-ghost" type="submit">Sign out</button>
                </form>
            @endguest
        </div>

        <details class="mobile-nav">
            <summary aria-label="Open navigation">Menu</summary>
            <div class="mobile-nav-panel">
                <a class="nav-link" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
                <a class="nav-link" href="{{ route('news.index') }}" @if(request()->routeIs('news.*')) aria-current="page" @endif>News</a>
                <a class="nav-link" href="{{ route('game.online.index') }}" @if(request()->routeIs('game.online.*')) aria-current="page" @endif>Online</a>
                <a class="nav-link" href="{{ route('game.highscores.index') }}" @if(request()->routeIs('game.highscores.*')) aria-current="page" @endif>Highscores</a>
                <a class="nav-link" href="{{ route('game.servers.index') }}" @if(request()->routeIs('game.servers.*')) aria-current="page" @endif>Servers</a>
                @guest
                    <a class="nav-link" href="{{ route('identity.login.create') }}">Sign in</a>
                    <a class="nav-link" href="{{ route('identity.register.create') }}">Create account</a>
                @else
                    <a class="nav-link" href="{{ route('account.overview') }}">Account overview</a>
                    <a class="nav-link" href="{{ route('identity.mfa.settings') }}">Account security</a>
                    <a class="nav-link" href="{{ route('identity.password.change.create') }}">Change password</a>
                    <form method="POST" action="{{ route('identity.logout') }}">
                        @csrf
                        <button class="button-ghost" type="submit">Sign out</button>
                    </form>
                @endguest
            </div>
        </details>
    </div>
</header>
<main id="main-content" class="page-shell @yield('page-class')">
    @yield('content')
</main>
<footer class="site-footer">
    <div class="site-footer-inner">Oteryn Platform · Public world information and account services</div>
</footer>
</body>
</html>
