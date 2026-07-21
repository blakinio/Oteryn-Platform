@extends('identity.layout')

@section('title', 'Register')
@section('error-title', 'Registration could not be completed.')

@section('content')
    <div class="page-header">
        <p class="eyebrow">New account</p>
        <h1>Create an Oteryn Platform identity</h1>
        <p class="muted">Your Platform identity owns web authentication and is used for supported account and character operations.</p>
    </div>

    <form class="form-stack" method="POST" action="{{ route('identity.register.store') }}">
        @csrf
        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="254" required autofocus>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" maxlength="1024" required>
        </div>
        <div class="form-field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" maxlength="1024" required>
        </div>
        <button type="submit">Register</button>
    </form>

    <nav class="identity-links" aria-label="Registration navigation">
        <a href="{{ route('identity.login.create') }}">Already registered? Sign in</a>
        <a href="{{ route('home') }}">Return to the public site</a>
    </nav>
@endsection
