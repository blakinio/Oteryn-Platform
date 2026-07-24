@extends('game.layout')

@section('title', 'Home')
@section('page-class', 'preview-home-shell production-home-shell')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home-preview.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home-production.css') }}">
@endpush

@section('content')
    <section class="preview-hero" aria-labelledby="home-hero-title">
        <div class="preview-hero-art" aria-hidden="true">
            <img src="{{ asset('images/oteryn-hero-citadel.svg') }}" alt="">
        </div>

        <div class="preview-hero-content">
            <div class="preview-hero-copy">
                <p class="preview-kicker">A realm of legends</p>
                <h1 id="home-hero-title"><span class="preview-sr-only">Oteryn Platform. </span>Answer the call of Oteryn</h1>
                <p class="preview-hero-lede">Ancient powers stir and new heroes rise. Follow the living world, read its chronicles and begin your own journey through one secure portal.</p>

                <div class="preview-hero-actions">
                    @guest
                        <a class="preview-button preview-button-primary" href="{{ route('identity.register.create') }}">Create account</a>
                        <a class="preview-button preview-button-secondary" href="{{ route('identity.login.create') }}">Sign in</a>
                    @else
                        <a class="preview-button preview-button-primary" href="{{ route('account.overview') }}">Open account center</a>
                    @endguest
                    <a class="preview-button preview-button-secondary" href="#realm-overview">View the realm</a>
                </div>
            </div>
        </div>
    </section>

    <section id="character-search" class="preview-search-wrap" aria-labelledby="home-character-search-heading">
        <div class="preview-search-card">
            <div class="preview-ornament" aria-hidden="true">
                <span></span>
                <img src="{{ asset('images/oteryn-sigil.svg') }}" alt="">
                <span></span>
            </div>
            <h2 id="home-character-search-heading">Find your character</h2>

            <form class="preview-search-form" method="GET" action="{{ route('game.characters.search') }}">
                <label class="preview-sr-only" for="home-character-name">Character name</label>
                <span class="preview-search-icon" aria-hidden="true">⌕</span>
                <input id="home-character-name" name="name" type="search" value="{{ old('name') }}" maxlength="255" autocomplete="off" placeholder="Enter an exact character name..." required>
                <button type="submit">Search</button>
            </form>

            @error('name')
                <p class="notice" role="alert">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <nav class="preview-realm-strip" aria-label="Realm shortcuts">
        <a href="{{ route('game.online.index') }}">
            <img src="{{ asset('images/oteryn-mark-online.svg') }}" alt="" aria-hidden="true">
            <span><strong>Online</strong><small>Open the current public list</small></span>
        </a>
        <a href="{{ route('game.highscores.index') }}">
            <img src="{{ asset('images/oteryn-mark-highscores.svg') }}" alt="" aria-hidden="true">
            <span><strong>Highscores</strong><small>Browse level rankings</small></span>
        </a>
        <a href="{{ route('game.servers.index') }}">
            <img src="{{ asset('images/oteryn-mark-servers.svg') }}" alt="" aria-hidden="true">
            <span><strong>Servers</strong><small>Inspect configured worlds</small></span>
        </a>
        <a href="{{ route('news.index') }}">
            <img src="{{ asset('images/oteryn-mark-news.svg') }}" alt="" aria-hidden="true">
            <span><strong>News</strong><small>Read published chronicles</small></span>
        </a>
    </nav>

    <section id="realm-overview" class="production-dashboard" aria-label="Oteryn public portal">
        <article class="preview-panel production-world-panel" data-content-state="{{ $homePage->world->state->value }}">
            <header class="preview-panel-heading">
                <div>
                    <p class="preview-panel-kicker">World activity</p>
                    <h2>World status</h2>
                </div>
                <span class="production-state-badge production-state-{{ strtolower($homePage->world->state->value) }}">{{ $homePage->world->state->value }}</span>
            </header>

            @switch($homePage->world->state)
                @case(\App\PublicPortal\PublicContentState::AVAILABLE)
                    <p class="production-world-total"><strong>{{ $homePage->world->playersOnline }}</strong> {{ Str::plural('player', $homePage->world->playersOnline ?? 0) }} online</p>
                    @break
                @case(\App\PublicPortal\PublicContentState::EMPTY)
                    <div class="production-state-message" role="status">No enabled worlds are configured.</div>
                    @break
                @case(\App\PublicPortal\PublicContentState::STALE)
                    <div class="production-state-message" role="status">World configuration is available, but one or more runtime records are stale or missing. An aggregate online count is intentionally not shown.</div>
                    @break
                @case(\App\PublicPortal\PublicContentState::UNAVAILABLE)
                    <div class="production-state-message" role="status">Live world data is temporarily unavailable. Configured metadata is shown when it could be read.</div>
                    @break
            @endswitch

            @if ($homePage->world->channels !== [])
                <div class="production-world-list">
                    @foreach ($homePage->world->channels as $channel)
                        <section class="production-world-row" aria-label="{{ $channel->name }} world status">
                            <div>
                                <h3>{{ $channel->name }}</h3>
                                <p>{{ $channel->pvpType }} · Capacity {{ $channel->maxPlayers }}</p>
                            </div>
                            <div class="production-runtime-summary">
                                @if ($homePage->world->state === \App\PublicPortal\PublicContentState::UNAVAILABLE)
                                    <strong>Unavailable</strong>
                                @elseif ($channel->runtimeStatus === null)
                                    <strong>Stale</strong>
                                @else
                                    <strong>{{ $channel->runtimeStatus }}</strong>
                                    <span>{{ $channel->playersOnline }} online</span>
                                @endif
                            </div>
                            @if ($channel->maintenance)
                                <p class="production-maintenance"><strong>Configured maintenance.</strong>@if ($channel->maintenanceMessage) {{ $channel->maintenanceMessage }}@endif</p>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif

            <a class="preview-panel-link" href="{{ route('game.servers.index') }}">View all servers</a>
        </article>

        <article class="preview-panel production-news-panel" data-content-state="{{ $homePage->news->state->value }}">
            <header class="preview-panel-heading">
                <div>
                    <p class="preview-panel-kicker">Chronicles</p>
                    <h2>Latest news</h2>
                </div>
                <span class="production-state-badge production-state-{{ strtolower($homePage->news->state->value) }}">{{ $homePage->news->state->value }}</span>
            </header>

            @if ($homePage->news->state === \App\PublicPortal\PublicContentState::AVAILABLE)
                <div class="production-news-list">
                    @foreach ($homePage->news->posts as $post)
                        <article>
                            <p class="production-news-date">{{ $post->published_at?->format('M j, Y') }}</p>
                            <h3><a href="{{ route('news.show', ['slug' => $post->slug]) }}">{{ $post->title }}</a></h3>
                            <p>{{ Str::limit($post->body, 170) }}</p>
                        </article>
                    @endforeach
                </div>
            @elseif ($homePage->news->state === \App\PublicPortal\PublicContentState::EMPTY)
                <div class="production-state-message" role="status">No news has been published yet.</div>
            @else
                <div class="production-state-message" role="status">Published news is temporarily unavailable.</div>
            @endif

            <a class="preview-panel-link" href="{{ route('news.index') }}">Read all news</a>
        </article>

        <article class="preview-panel production-path-panel">
            <header class="preview-panel-heading">
                <div>
                    <p class="preview-panel-kicker">Begin your journey</p>
                    <h2>Your Oteryn path</h2>
                </div>
                <img src="{{ asset('images/oteryn-sigil.svg') }}" alt="" aria-hidden="true">
            </header>
            <div class="production-path-list">
                @guest
                    <a href="{{ route('identity.register.create') }}"><strong>Create an account</strong><span>Establish your secure Platform identity.</span></a>
                    <a href="{{ route('identity.login.create') }}"><strong>Sign in</strong><span>Return to your account and characters.</span></a>
                @else
                    <a href="{{ route('account.overview') }}"><strong>Account center</strong><span>Review account readiness and characters.</span></a>
                    <a href="{{ route('account.characters.create') }}"><strong>Create a character</strong><span>Begin a new adventure through the approved flow.</span></a>
                @endguest
                <a href="{{ route('game.highscores.index') }}"><strong>Meet the heroes</strong><span>Explore the current public rankings.</span></a>
            </div>
        </article>
    </section>
@endsection
