@extends('admin.layout')

@section('title', $post === null ? 'Create News Post' : 'Edit News Post')

@section('content')
    <h2>{{ $post === null ? 'Create news post' : 'Edit news post' }}</h2>

    <form method="POST" action="{{ $post === null ? route('admin.news.store') : route('admin.news.update', $post) }}">
        @csrf
        @if ($post !== null)
            @method('PUT')
        @endif

        <div>
            <label for="slug">Slug</label>
            <input id="slug" name="slug" type="text" maxlength="160" required value="{{ old('slug', $post?->slug) }}">
        </div>

        <div>
            <label for="title">Title</label>
            <input id="title" name="title" type="text" maxlength="200" required value="{{ old('title', $post?->title) }}">
        </div>

        <div>
            <label for="body">Body (plain text)</label>
            <textarea id="body" name="body" rows="20" maxlength="100000" required>{{ old('body', $post?->body) }}</textarea>
        </div>

        <div>
            <label for="published_at">Publish at</label>
            <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}">
            <p>Leave empty to keep this post as a draft.</p>
        </div>

        <button type="submit">Save</button>
    </form>
@endsection
