@extends('game.layout')

@section('title', 'Highscores')

@section('content')
    <h1>Level highscores</h1>
    <p class="muted">Active characters only. Rankings are global across configured channels.</p>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Rank</th>
                <th>Character</th>
                <th>Level</th>
                <th>Vocation ID</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($players as $player)
                <tr>
                    <td>{{ $players->firstItem() + $loop->index }}</td>
                    <td><a href="{{ route('game.characters.show', ['name' => $player->name]) }}">{{ $player->name }}</a></td>
                    <td>{{ $player->level }}</td>
                    <td>{{ $player->vocation }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No active characters found.</td></tr>
            @endforelse
            </tbody>
        </table>

        @if ($players->hasPages())
            <nav class="pagination" aria-label="Highscore pages">
                @if ($players->onFirstPage())
                    <span class="muted">Previous</span>
                @else
                    <a href="{{ $players->previousPageUrl() }}">Previous</a>
                @endif
                <span>Page {{ $players->currentPage() }} of {{ $players->lastPage() }}</span>
                @if ($players->hasMorePages())
                    <a href="{{ $players->nextPageUrl() }}">Next</a>
                @else
                    <span class="muted">Next</span>
                @endif
            </nav>
        @endif
    </div>
@endsection
