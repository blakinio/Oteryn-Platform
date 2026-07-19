<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; background: #111827; color: #f9fafb; }
        a { color: #93c5fd; }
        header, main { width: min(70rem, calc(100% - 2rem)); margin: 0 auto; }
        header { display: flex; gap: 1rem; align-items: center; justify-content: space-between; padding: 1.25rem 0; }
        nav { display: flex; gap: 1rem; flex-wrap: wrap; }
        main { padding: 1rem 0 3rem; }
        .card { padding: 1.25rem; margin-bottom: 1rem; border: 1px solid #374151; border-radius: .75rem; background: #1f2937; }
        .muted { color: #9ca3af; }
        .notice { padding: .9rem 1rem; border-left: .25rem solid #60a5fa; background: #1e3a5f; }
        .search-row { display: flex; gap: .75rem; flex-wrap: wrap; align-items: center; }
        input, button { font: inherit; padding: .7rem .8rem; border: 1px solid #4b5563; border-radius: .5rem; }
        input { min-width: min(24rem, 100%); background: #111827; color: #f9fafb; }
        button { cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .75rem; text-align: left; border-bottom: 1px solid #374151; }
        .pagination { display: flex; gap: .75rem; align-items: center; margin-top: 1rem; }
        .status { font-weight: 700; }
    </style>
</head>
<body>
<header>
    <a href="{{ route('home') }}"><strong>Oteryn Platform</strong></a>
    <nav aria-label="Public game data">
        <a href="{{ route('home') }}">Home</a>
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
