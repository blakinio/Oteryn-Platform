@extends('game.layout')

@section('title', 'Home')
@section('page-class', 'portal-home-shell')

@section('content')
    <section class="portal-hero" aria-labelledby="portal-hero-title">
        <div class="portal-hero-inner">
            <div class="portal-hero-copy">
                <p class="portal-kicker">Oteryn Platform · World portal</p>
                <h1 id="portal-hero-title" class="portal-hero-title">Enter the world of Oteryn</h1>
                <p class="portal-hero-lede">Find characters, follow world activity, explore rankings and move between public world information and your account from one deliberate portal.</p>
                <div class="portal-hero-actions">
                    @guest
                        <a class="button" href="{{ route('identity.register.create') }}">Create account</a>
                    @else
                        <a class="button" href="{{ route('account.overview') }}">Open account center</a>
                    @endguest
                    <a class="button button-secondary" href="#world-information">Explore the world</a>
                </div>
            </div>

            <section class="portal-search-card" aria-labelledby="character-search-heading">
                <div class="portal-search-heading">
                    <img src="{{ asset('images/oteryn-sigil.svg') }}" alt="" aria-hidden="true">
                    <div>
                        <p class="eyebrow">World records</p>
                        <h2 id="character-search-heading">Find a character</h2>
                    </div>
                </div>
                <form method="GET" action="{{ route('game.characters.search') }}">
                    <div class="form-field">
                        <label for="character-name">Character name</label>
                        <input id="character-name" name="name" type="search" value="{{ old('name') }}" maxlength="255" autocomplete="off" required>
                        <p class="form-help">Search the public character directory by exact name.</p>
                    </div>
                    @error('name')
                        <p class="notice" role="alert">{{ $message }}</p>
                    @enderror
                    <button type="submit">Search character</button>
                </form>
            </section>
        </div>
    </section>

    <section id="world-information" class="portal-world-section" aria-labelledby="public-data-heading">
        <div class="portal-world-heading">
            <div class="page-header">
                <p class="eyebrow">Explore Oteryn</p>
                <h2 id="public-data-heading">World information</h2>
                <p class="muted">Move directly to the public views that describe the current Oteryn world.</p>
            </div>
        </div>

        <div class="portal-world-grid">
            <article class="world-card">
                <div class="world-card-header">
                    <span class="world-card-icon" aria-hidden="true">O</span>
                    <h3>Online</h3>
                </div>
                <p>See characters represented by fresh online leases and review the current public online view.</p>
                <a class="world-card-link" href="{{ route('game.online.index') }}">View online characters</a>
            </article>

            <article class="world-card">
                <div class="world-card-header">
                    <span class="world-card-icon" aria-hidden="true">H</span>
                    <h3>Highscores</h3>
                </div>
                <p>Browse the active-character level ranking through the public highscores surface.</p>
                <a class="world-card-link" href="{{ route('game.highscores.index') }}">View highscores</a>
            </article>

            <article class="world-card">
                <div class="world-card-header">
                    <span class="world-card-icon" aria-hidden="true">S</span>
                    <h3>Servers</h3>
                </div>
                <p>Review configured game channels and the live runtime information currently available to the portal.</p>
                <a class="world-card-link" href="{{ route('game.servers.index') }}">View servers</a>
            </article>

            <article class="world-card">
                <div class="world-card-header">
                    <span class="world-card-icon" aria-hidden="true">N</span>
                    <h3>News</h3>
                </div>
                <p>Read published Platform news and world updates without inventing unverified events or gameplay claims.</p>
                <a class="world-card-link" href="{{ route('news.index') }}">View latest news</a>
            </article>
        </div>

        <p class="portal-world-note">Public world data remains read-only. Account and security operations stay in the dedicated Account Center.</p>
    </section>
@endsection
