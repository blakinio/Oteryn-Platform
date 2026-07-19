@extends('game.layout')

@section('title', 'News')

@section('content')
    <h1>News</h1>
    <p class="muted">Published Oteryn Platform updates.</p>

    @forelse ($posts as $post)
        <article class="card">
            <h2><a href="{{ route('news.show', ['slug' => $post->slug]) }}">{{ $post->title }}</a></h2>
            <p class="muted">Published {{ $post->published_at?->format('Y-m-d H:i') }}</p>
        </article>
    @empty
        <div class="card">No published news yet.</div>
    @endforelse

    @if ($posts->hasPages())
        <nav class="pagination" aria-label="News pages">
            @if ($posts->onFirstPage())
                <span class="muted">Previous</span>
            @else
                <a href="{{ $posts->previousPageUrl() }}">Previous</a>
            @endif
            <span>Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</span>
            @if ($posts->hasMorePages())
                <a href="{{ $posts->nextPageUrl() }}">Next</a>
            @else
                <span class="muted">Next</span>
            @endif
        </nav>
    @endif
@endsection
