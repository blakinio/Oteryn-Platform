@extends('game.layout')

@section('title', 'Home')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Oteryn world portal</p>
        <h1>Explore Oteryn</h1>
        <p class="muted">Find characters, check who is online, browse rankings and review configured game servers.</p>
    </div>

    <section class="card" aria-labelledby="character-search-heading">
        <h2 id="character-search-heading">Find a character</h2>
        <p class="muted">Search by exact character name.</p>
        <form method="GET" action="{{ route('game.characters.search') }}">
            <div class="search-row">
                <label for="character-name">Character name</label>
                <input id="character-name" name="name" type="search" value="{{ old('name') }}" maxlength="255" required>
                <button type="submit">Search</button>
            </div>
            @error('name')
                <p class="notice">{{ $message }}</p>
            @enderror
        </form>
    </section>

    <section aria-labelledby="public-data-heading">
        <div class="page-header">
            <h2 id="public-data-heading">World information</h2>
            <p class="muted">Public read-only views of the current Oteryn game world.</p>
        </div>
        <div class="card-grid">
            <article class="card">
                <h2>Online</h2>
                <p class="muted">See characters with fresh online leases.</p>
                <a href="{{ route('game.online.index') }}">View online characters</a>
            </article>
            <article class="card">
                <h2>Highscores</h2>
                <p class="muted">Browse the active-character level ranking.</p>
                <a href="{{ route('game.highscores.index') }}">View highscores</a>
            </article>
            <article class="card">
                <h2>Servers</h2>
                <p class="muted">Review configured channels and available live runtime information.</p>
                <a href="{{ route('game.servers.index') }}">View servers</a>
            </article>
        </div>
    </section>
@endsection
