@extends('identity.layout')

@section('title', 'Forgot Password')
@section('error-title', 'The reset request could not be completed.')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Account recovery</p>
        <h1>Reset your Oteryn Platform password</h1>
        <p class="muted">Enter your email address. The public response remains the same whether or not an account exists.</p>
    </div>

    <form class="form-stack" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="254" required autofocus>
        </div>
        <button type="submit">Send reset link</button>
    </form>

    <nav class="identity-links" aria-label="Password recovery navigation">
        <a href="{{ route('identity.login.create') }}">Return to sign in</a>
        <a href="{{ route('identity.register.create') }}">Create an account</a>
    </nav>
@endsection
