<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header>
    <a href="{{ route('home') }}"><strong>Oteryn Platform</strong></a>
    <nav aria-label="Public navigation">
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('news.index') }}">News</a>
        <a href="{{ route('game.online.index') }}">Online</a>
        <a href="{{ route('game.highscores.index') }}">Highscores</a>
        <a href="{{ route('game.servers.index') }}">Servers</a>
    </nav>
</header>
<main>
    @yield('content')
</main>
</body>
</html>
