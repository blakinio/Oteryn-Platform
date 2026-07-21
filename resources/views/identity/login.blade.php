@extends('identity.layout')

@section('title', 'Login')
@section('error-title', 'Sign in could not be completed.')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Account access</p>
        <h1>Sign in to Oteryn Platform</h1>
        <p class="muted">Use your Platform identity to manage account security and create characters.</p>
    </div>

    <form class="form-stack" method="POST" action="{{ route('identity.login.store') }}">
        @csrf
        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="254" required autofocus>
        </div>
        <div class="form-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" maxlength="1024" required>
        </div>
        <button type="submit">Sign in</button>
    </form>

    <nav class="identity-links" aria-label="Sign in help">
        <a href="{{ route('password.request') }}">Forgot your password?</a>
        <a href="{{ route('identity.register.create') }}">Create an account</a>
        <a href="{{ route('home') }}">Return to the public site</a>
    </nav>
@endsection
