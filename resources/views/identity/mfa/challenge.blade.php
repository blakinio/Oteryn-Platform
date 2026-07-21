@extends('identity.layout')

@section('title', 'MFA challenge')
@section('error-title', 'Sign in could not be completed.')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Second factor</p>
        <h1>Complete your sign in</h1>
        <p class="muted">Enter a fresh six-digit authenticator code or one unused recovery code.</p>
    </div>

    <form class="form-stack" method="POST" action="{{ route('identity.mfa.challenge.store') }}">
        @csrf
        <div class="form-field">
            <label for="code">Authenticator or recovery code</label>
            <input id="code" name="code" type="text" autocomplete="one-time-code" maxlength="64" required autofocus>
        </div>
        <button type="submit">Verify and sign in</button>
    </form>

    <nav class="identity-links" aria-label="MFA challenge navigation">
        <a href="{{ route('identity.login.create') }}">Start sign in again</a>
        <a href="{{ route('home') }}">Return to the public site</a>
    </nav>
@endsection
