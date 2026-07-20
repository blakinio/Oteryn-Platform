@extends('admin.layout')

@section('title', 'Manage News')

@section('content')
    <h2>News</h2>
    <p><a href="{{ route('admin.news.create') }}">Create news post</a></p>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Slug</th>
                <th>Publication</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($posts as $post)
                <tr>
                    <td>{{ $post->title }}</td>
                    <td>{{ $post->slug }}</td>
                    <td>{{ $post->published_at?->format('Y-m-d H:i') ?? 'Draft' }}</td>
                    <td><a href="{{ route('admin.news.edit', $post) }}">Edit</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $posts->links() }}
@endsection
