@extends('errors.layout')

@section('title', 'Service unavailable')
@section('code', '503')
@section('heading', 'Oteryn is temporarily unavailable')
@section('message', 'A required service is unavailable or maintenance is in progress. Please try again later; your request has not been reported as successful.')

@section('actions')
    <a class="button" href="{{ route('home') }}">Try the public site</a>
@endsection
