@extends('game.layout')

@section('title', 'Servers')

@section('content')
    <h1>Servers</h1>
    <p class="muted">Configured channel metadata with fresh Canary runtime availability when the dedicated runtime dependency is available.</p>

    @if (! $runtimeSnapshot->available)
        <p class="notice">Live runtime status is temporarily unavailable. Configured channel metadata remains available below.</p>
    @endif

    @forelse ($channels as $channel)
        @php($runtime = $runtimeSnapshot->forChannel((int) $channel->id))
        <article class="card">
            <h2>{{ $channel->name }}</h2>
            <p><strong>Channel ID:</strong> {{ $channel->id }}</p>
            <p><strong>PvP type:</strong> {{ $channel->pvp_type }}</p>
            <p><strong>Configured max players:</strong> {{ $channel->max_players }}</p>

            @if (! $runtimeSnapshot->available)
                <p><strong>Runtime:</strong> Unavailable</p>
            @elseif ($runtime === null)
                <p><strong>Runtime:</strong> Unknown</p>
            @else
                <p><strong>Runtime:</strong> {{ $runtime->status }}</p>
                <p><strong>Players online:</strong> {{ $runtime->playersOnline }}</p>
                @if ($runtime->isFull((int) $channel->max_players))
                    <p class="status">Full</p>
                @endif
            @endif

            @if ($channel->maintenance)
                <p class="status">Configured maintenance</p>
                @if ($channel->maintenance_message)
                    <p>{{ $channel->maintenance_message }}</p>
                @endif
            @endif
        </article>
    @empty
        <div class="card">No enabled channels are configured.</div>
    @endforelse
@endsection
