@extends('game.layout')

@section('title', 'Servers')

@section('content')
    <h1>Configured channels</h1>
    <p class="notice">This page shows configured channel metadata and maintenance state only. Cluster-wide live player availability is intentionally not shown because its source and freshness contract are not yet proven.</p>

    @forelse ($channels as $channel)
        <article class="card">
            <h2>{{ $channel->name }}</h2>
            <p><strong>Channel ID:</strong> {{ $channel->id }}</p>
            <p><strong>PvP type:</strong> {{ $channel->pvp_type }}</p>
            <p><strong>Configured max players:</strong> {{ $channel->max_players }}</p>
            @if ($channel->maintenance)
                <p class="status">Maintenance</p>
                @if ($channel->maintenance_message)
                    <p>{{ $channel->maintenance_message }}</p>
                @endif
            @else
                <p class="status">Configured</p>
            @endif
        </article>
    @empty
        <div class="card">No enabled channels are configured.</div>
    @endforelse
@endsection
