@extends('game.layout')

@section('title', 'Servers')

@section('content')
    <div class="page-header">
        <p class="eyebrow">World infrastructure</p>
        <h1>Servers</h1>
        <p class="muted">Configured channel metadata with fresh Canary runtime availability when the dedicated runtime dependency is available.</p>
    </div>

    @if (! $runtimeSnapshot->available)
        <div class="alert alert-warning" role="status">The live runtime dependency is temporarily unavailable, so live player availability is intentionally not shown. Configured channel metadata remains available below.</div>
    @endif

    <div class="card-grid">
        @forelse ($channels as $channel)
            @php($runtime = $runtimeSnapshot->forChannel((int) $channel->id))
            <article class="card">
                <h2>{{ $channel->name }}</h2>
                <dl>
                    <dt>Channel ID</dt><dd>{{ $channel->id }}</dd>
                    <dt>PvP type</dt><dd>{{ $channel->pvp_type }}</dd>
                    <dt>Configured max players</dt><dd>{{ $channel->max_players }}</dd>
                    @if (! $runtimeSnapshot->available)
                        <dt>Runtime</dt><dd><span class="badge badge-warning">Unavailable</span></dd>
                    @elseif ($runtime === null)
                        <dt>Runtime</dt><dd><span class="badge badge-warning">Unknown</span></dd>
                    @else
                        <dt>Runtime</dt><dd><span class="badge badge-success">{{ $runtime->status }}</span></dd>
                        <dt>Players online</dt><dd>{{ $runtime->playersOnline }}</dd>
                    @endif
                </dl>

                @if ($runtimeSnapshot->available && $runtime !== null && $runtime->isFull((int) $channel->max_players))
                    <p class="status badge badge-warning">Full</p>
                @endif

                @if ($channel->maintenance)
                    <div class="alert alert-warning">
                        <strong>Configured maintenance</strong>
                        @if ($channel->maintenance_message)
                            <p>{{ $channel->maintenance_message }}</p>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="empty-state">No enabled channels are configured.</div>
        @endforelse
    </div>
@endsection
