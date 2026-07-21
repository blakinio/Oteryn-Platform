@extends('identity.layout')

@section('title', 'MFA recovery codes')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Account security</p>
        <h1>Save your recovery codes now</h1>
        <p class="muted">Each recovery code can be used once. These plaintext codes are shown only in this response.</p>
    </div>

    <div class="alert alert-warning">
        Store these codes somewhere private before leaving this page. They are intended for account recovery when your authenticator is unavailable.
    </div>

    <ul class="recovery-codes" aria-label="MFA recovery codes">
        @foreach ($recoveryCodes as $recoveryCode)
            <li><code>{{ $recoveryCode }}</code></li>
        @endforeach
    </ul>

    <div class="action-row">
        <a class="button" href="{{ route('identity.mfa.settings') }}">Return to MFA settings</a>
        <a class="button button-secondary" href="{{ route('home') }}">Go to public site</a>
    </div>
@endsection
