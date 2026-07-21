@extends('game.layout')

@section('title', $guild->name)
@section('page-class', 'page-shell-wide')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Guild</p>
        <h1>{{ $guild->name }}</h1>
    </div>

    <div class="card stat-grid">
        <div class="stat"><strong>Guild level</strong><br>{{ $guild->level }}</div>
        <div class="stat"><strong>Points</strong><br>{{ $guild->points }}</div>
        <div class="stat"><strong>Residence ID</strong><br>{{ $guild->residence }}</div>
        <div class="stat"><strong>Owner player ID</strong><br>{{ $guild->ownerid }}</div>
        @if ($guild->motd !== '')
            <div class="stat"><strong>Message</strong><br>{{ $guild->motd }}</div>
        @endif
    </div>

    <section aria-labelledby="guild-members-heading">
        <div class="page-header">
            <h2 id="guild-members-heading">Members</h2>
        </div>
        <div class="card">
            <div class="table-region" tabindex="0" aria-label="Guild members table, horizontally scrollable on small screens">
                <table>
                    <thead>
                    <tr>
                        <th scope="col">Character</th>
                        <th scope="col">Rank</th>
                        <th scope="col">Nickname</th>
                        <th scope="col">Level</th>
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
            </div>

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
    </section>
@endsection
