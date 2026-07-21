@extends('identity.layout')

@section('title', 'Account overview')

@section('content')
    <header class="page-header">
        <p class="eyebrow">Account center</p>
        <h1>Account overview</h1>
        <p class="muted">Review your Platform account and the current state of game account setup.</p>
    </header>

    @php
        $badgeClass = match ($overview['state']) {
            'ready' => 'badge-success',
            'pending', 'recoverable' => 'badge-warning',
            default => 'badge-danger',
        };
    @endphp

    <div class="card-grid">
        <section class="card" aria-labelledby="game-account-heading">
            <p class="eyebrow">Game account</p>
            <h2 id="game-account-heading">Provisioning status</h2>
            <p><span class="badge {{ $badgeClass }}">{{ $overview['label'] }}</span></p>
            <p>{{ $overview['message'] }}</p>

            <div class="action-row">
                @if ($overview['character_creation_allowed'])
                    <a class="button" href="{{ route('account.characters.create') }}">Create a character</a>
                @endif

                @if ($overview['retry_allowed'])
                    <form method="POST" action="{{ route('account.provisioning.retry') }}">
                        @csrf
                        <button type="submit">Retry game account setup</button>
                    </form>
                @endif
            </div>
        </section>

        <section class="card" aria-labelledby="account-security-heading">
            <p class="eyebrow">Platform account</p>
            <h2 id="account-security-heading">Security and access</h2>
            <dl>
                <dt>Email</dt>
                <dd>{{ $identity->email }}</dd>
                <dt>MFA</dt>
                <dd>{{ $identity->hasConfirmedMfa() ? 'Enabled' : 'Not enabled' }}</dd>
            </dl>
            <div class="action-row">
                <a class="button button-secondary" href="{{ route('identity.mfa.settings') }}">Manage security</a>
                <a class="button button-secondary" href="{{ route('identity.password.change.create') }}">Change password</a>
            </div>
        </section>
    </div>

    @unless ($overview['character_creation_allowed'])
        <div class="notice" role="status">
            Character creation stays unavailable until the Platform can confirm a ready game account binding.
        </div>
    @endunless
@endsection