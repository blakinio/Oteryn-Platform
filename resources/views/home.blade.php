<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #111827; color: #f9fafb; }
        main { width: min(42rem, calc(100% - 3rem)); padding: 3rem; border: 1px solid #374151; border-radius: 1rem; background: #1f2937; }
        .eyebrow { margin: 0 0 .75rem; font-size: .75rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #93c5fd; }
        h1 { margin: 0 0 1rem; font-size: clamp(2rem, 6vw, 3.5rem); line-height: 1.05; }
        p { line-height: 1.7; color: #d1d5db; }
        nav { display: flex; gap: 1rem; margin-top: 1.5rem; }
        a, code { color: #bfdbfe; }
    </style>
</head>
<body>
<main>
    <p class="eyebrow">Oteryn Platform</p>
    <h1>Laravel 13 foundation is online.</h1>
    <p>The initial read-only public game-data surfaces are available. Infrastructure monitoring can use <code>GET /health</code>.</p>
    <nav aria-label="Public game data">
        <a href="{{ route('game.highscores.index') }}">Highscores</a>
        <a href="{{ route('game.servers.index') }}">Servers</a>
    </nav>
</main>
</body>
</html>
