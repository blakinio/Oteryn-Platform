@extends('game.layout')

@section('title', 'News')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Updates</p>
        <h1>News</h1>
        <p class="muted">Published Oteryn Platform updates.</p>
    </div>

    @forelse ($posts as $post)
        <article class="card">
            <p class="eyebrow">Published {{ $post->published_at?->format('Y-m-d H:i') }}</p>
            <h2><a href="{{ route('news.show', ['slug' => $post->slug]) }}">{{ $post->title }}</a></h2>
            <a href="{{ route('news.show', ['slug' => $post->slug]) }}">Read update</a>
        </article>
    @empty
        <div class="empty-state">
            <strong>No published news yet.</strong>
            <p>Published Oteryn updates will appear here.</p>
        </div>
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
