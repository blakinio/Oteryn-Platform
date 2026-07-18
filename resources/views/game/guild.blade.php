@extends('game.layout')

@section('title', $guild->name)

@section('content')
    <h1>{{ $guild->name }}</h1>

    <div class="card">
        <p><strong>Guild level:</strong> {{ $guild->level }}</p>
        <p><strong>Points:</strong> {{ $guild->points }}</p>
        <p><strong>Residence ID:</strong> {{ $guild->residence }}</p>
        <p><strong>Owner player ID:</strong> {{ $guild->ownerid }}</p>
        @if ($guild->motd !== '')
            <p><strong>Message:</strong> {{ $guild->motd }}</p>
        @endif
    </div>

    <h2>Members</h2>
    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Character</th>
                <th>Rank</th>
                <th>Nickname</th>
                <th>Level</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($members as $member)
                <tr>
                    <td><a href="{{ route('game.characters.show', ['name' => $member->name]) }}">{{ $member->name }}</a></td>
                    <td>{{ $member->rank_name }}</td>
                    <td>{{ $member->nick ?: '—' }}</td>
                    <td>{{ $member->level }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No active members found.</td></tr>
            @endforelse
            </tbody>
        </table>

        @if ($members->hasPages())
            <nav class="pagination" aria-label="Guild member pages">
                @if ($members->onFirstPage())
                    <span class="muted">Previous</span>
                @else
                    <a href="{{ $members->previousPageUrl() }}">Previous</a>
                @endif
                <span>Page {{ $members->currentPage() }} of {{ $members->lastPage() }}</span>
                @if ($members->hasMorePages())
                    <a href="{{ $members->nextPageUrl() }}">Next</a>
                @else
                    <span class="muted">Next</span>
                @endif
            </nav>
        @endif
    </div>
@endsection
