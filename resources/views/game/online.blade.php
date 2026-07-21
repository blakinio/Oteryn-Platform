@extends('game.layout')

@section('title', 'Online characters')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Live world</p>
        <h1>Online characters</h1>
        <p class="muted">This cluster-wide list includes only fresh ONLINE character leases that have not expired at read time.</p>
    </div>

    <div class="card-grid">
        @forelse ($characters as $character)
            <article class="card">
                <h2><a href="{{ route('game.characters.show', ['name' => $character->name]) }}">{{ $character->name }}</a></h2>
                <dl>
                    <dt>Level:</dt><dd>{{ $character->level }}</dd>
                    <dt>Vocation:</dt><dd>{{ $character->vocation }}</dd>
                    <dt>Channel:</dt><dd>{{ $character->channel_name }} (ID {{ $character->channel_id }})</dd>
                </dl>
            </article>
        @empty
            <div class="empty-state">No characters are currently online.</div>
        @endforelse
    </div>

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
