@extends('game.layout')

@section('title', $post->title)

@section('content')
    <article>
        <h1>{{ $post->title }}</h1>
        <p class="muted">Published {{ $post->published_at?->format('Y-m-d H:i') }}</p>

        <div class="card">
            <p class="prose-text">{{ $post->body }}</p>
        </div>
    </article>
@endsection
