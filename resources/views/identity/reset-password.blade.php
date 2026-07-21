@extends('identity.layout')

@section('title', 'Reset Password')
@section('error-title', 'The password could not be reset.')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Account recovery</p>
        <h1>Choose a new Oteryn Platform password</h1>
        <p class="muted">Complete this one-time reset to replace your Platform password and revoke older web sessions.</p>
    </div>

    <form class="form-stack" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input name="token" type="hidden" value="{{ $token }}">
        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" maxlength="254" required>
        </div>
        <div class="form-field">
            <label for="password">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" maxlength="1024" required>
        </div>
        <div class="form-field">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" maxlength="1024" required>
        </div>
        <button type="submit">Reset password</button>
    </form>

    <nav class="identity-links" aria-label="Password reset navigation">
        <a href="{{ route('identity.login.create') }}">Return to sign in</a>
    </nav>
@endsection
