@extends('game.layout')

@section('title', $page->title)

@section('content')
    <article>
        <h1>{{ $page->title }}</h1>

        <div class="card">
            <p class="prose-text">{{ $page->body }}</p>
        </div>
    </article>
@endsection
