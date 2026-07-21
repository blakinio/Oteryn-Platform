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
                <p><strong>Channel ID:</strong> {{ $channel->id }}</p>
                <p><strong>PvP type:</strong> {{ $channel->pvp_type }}</p>
                <p><strong>Configured max players:</strong> {{ $channel->max_players }}</p>

                @if (! $runtimeSnapshot->available)
                    <p class="badge badge-warning"><strong>Runtime:</strong> Unavailable</p>
                @elseif ($runtime === null)
                    <p class="badge badge-warning"><strong>Runtime:</strong> Unknown</p>
                @else
                    <p class="badge badge-success"><strong>Runtime:</strong> {{ $runtime->status }}</p>
                    <p><strong>Players online:</strong> {{ $runtime->playersOnline }}</p>
                @endif

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
