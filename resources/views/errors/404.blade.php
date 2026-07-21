@extends('errors.layout')

@section('title', 'Page not found')
@section('code', '404')
@section('heading', 'We could not find that page')
@section('message', 'The address may be incorrect or the content may no longer be available.')

@section('actions')
    <a class="button" href="{{ route('home') }}">Go to home</a>
    <a class="button button-secondary" href="{{ route('news.index') }}">Browse news</a>
@endsection
