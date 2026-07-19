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

    @if ($characters->hasPages())
        <nav class="pagination" aria-label="Online character pages">
            @if ($characters->onFirstPage())
                <span class="muted">Previous</span>
            @else
                <a href="{{ $characters->previousPageUrl() }}">Previous</a>
            @endif
            <span>Page {{ $characters->currentPage() }} of {{ $characters->lastPage() }}</span>
            @if ($characters->hasMorePages())
                <a href="{{ $characters->nextPageUrl() }}">Next</a>
            @else
                <span class="muted">Next</span>
            @endif
        </nav>
    @endif
@endsection
