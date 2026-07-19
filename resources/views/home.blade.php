@extends('game.layout')

@section('title', 'Home')

@section('content')
    <h1>Oteryn Platform</h1>
    <p class="muted">Laravel 13 foundation is online. Browse the implemented read-only game-data surfaces without requiring shared Canary write access.</p>

    <section class="card" aria-labelledby="character-search-heading">
        <h2 id="character-search-heading">Find a character</h2>
        <p class="muted">Search by exact character name.</p>

        <form method="GET" action="{{ route('game.characters.search') }}">
            <div class="search-row">
                <label for="character-name">Character name</label>
                <input
                    id="character-name"
                    name="name"
                    type="search"
                    value="{{ old('name') }}"
                    maxlength="255"
                    required
                >
                <button type="submit">Search</button>
            </div>

            @error('name')
                <p class="notice">{{ $message }}</p>
            @enderror
        </form>
    </section>

    <section class="card" aria-labelledby="public-data-heading">
        <h2 id="public-data-heading">Public game data</h2>
        <p>
            <a href="{{ route('game.online.index') }}">Online characters</a>,
            <a href="{{ route('game.highscores.index') }}">level highscores</a>, and
            <a href="{{ route('game.servers.index') }}">configured servers</a>
            are available through the current read-only public surface.
        </p>
    </section>
@endsection
