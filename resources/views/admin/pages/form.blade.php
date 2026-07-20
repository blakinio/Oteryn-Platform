@extends('admin.layout')

@section('title', $page === null ? 'Create Managed Page' : 'Edit Managed Page')

@section('content')
    <h2>{{ $page === null ? 'Create managed page' : 'Edit managed page' }}</h2>

    <form method="POST" action="{{ $page === null ? route('admin.pages.store') : route('admin.pages.update', $page) }}">
        @csrf
        @if ($page !== null)
            @method('PUT')
        @endif

        <div>
            <label for="slug">Slug</label>
            <input id="slug" name="slug" type="text" maxlength="160" required value="{{ old('slug', $page?->slug) }}">
        </div>

        <div>
            <label for="title">Title</label>
            <input id="title" name="title" type="text" maxlength="200" required value="{{ old('title', $page?->title) }}">
        </div>

        <div>
            <label for="body">Body (plain text)</label>
            <textarea id="body" name="body" rows="20" maxlength="100000" required>{{ old('body', $page?->body) }}</textarea>
        </div>

        <div>
            <label for="published_at">Publish at</label>
            <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $page?->published_at?->format('Y-m-d\TH:i')) }}">
            <p>Leave empty to keep this page as a draft.</p>
        </div>

        <button type="submit">Save</button>
    </form>
@endsection
