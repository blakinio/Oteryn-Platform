<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA settings | Oteryn Platform</title>
</head>
<body>
    <main>
        <h1>Multi-factor authentication</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($identity->hasConfirmedMfa())
            <p>MFA is enabled for your Oteryn Platform web sign in.</p>
            <p>Disabling MFA signs out every Platform web session.</p>

            <form method="POST" action="{{ route('identity.mfa.destroy') }}">
                @csrf
                @method('DELETE')
                <div>
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" maxlength="1024" required>
                </div>
                <div>
                    <label for="code">Fresh authenticator or recovery code</label>
                    <input id="code" name="code" type="text" autocomplete="one-time-code" maxlength="64" required>
                </div>
                <button type="submit">Disable MFA and sign out everywhere</button>
            </form>
        @elseif (is_string($identity->two_factor_secret))
            <p>MFA enrollment is not active until you confirm a code from your authenticator app.</p>
            <p>Manual secret: <code>{{ $identity->two_factor_secret }}</code></p>
            <p>Provisioning URI: <code>{{ $provisioningUri }}</code></p>

            <form method="POST" action="{{ route('identity.mfa.confirm') }}">
                @csrf
                <div>
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" maxlength="1024" required>
                </div>
                <div>
                    <label for="code">Six-digit authenticator code</label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                </div>
                <button type="submit">Confirm and enable MFA</button>
            </form>
        @else
            <p>MFA is not enabled. Enabling it will require a second factor for future Oteryn Platform web sign ins.</p>
            <form method="POST" action="{{ route('identity.mfa.enroll') }}">
                @csrf
                <button type="submit">Start MFA enrollment</button>
            </form>
        @endif
    </main>
</body>
</html>
