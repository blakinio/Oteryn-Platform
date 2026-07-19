@extends('game.layout')

@section('title', 'Online characters')

@section('content')
    <h1>Online characters</h1>
    <p class="notice">This cluster-wide list includes only fresh ONLINE character leases that have not expired at read time.</p>

    @forelse ($characters as $character)
        <article class="card">
            <h2>{{ $character->name }}</h2>
            <p><strong>Level:</strong> {{ $character->level }}</p>
            <p><strong>Vocation:</strong> {{ $character->vocation }}</p>
            <p><strong>Channel:</strong> {{ $character->channel_name }} (ID {{ $character->channel_id }})</p>
        </article>
    @empty
        <div class="card">No characters are currently online.</div>
    @endforelse
@endsection
