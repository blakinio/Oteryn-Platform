@extends('admin.layout')

@section('title', 'Manage Pages')

@section('content')
    <h2>Managed pages</h2>
    <p><a href="{{ route('admin.pages.create') }}">Create managed page</a></p>

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
            @foreach ($pages as $page)
                <tr>
                    <td>{{ $page->title }}</td>
                    <td>{{ $page->slug }}</td>
                    <td>{{ $page->published_at?->format('Y-m-d H:i') ?? 'Draft' }}</td>
                    <td><a href="{{ route('admin.pages.edit', $page) }}">Edit</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $pages->links() }}
@endsection
