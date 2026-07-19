<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA recovery codes | Oteryn Platform</title>
</head>
<body>
    <main>
        <h1>Save your recovery codes now</h1>
        <p>Each recovery code can be used once. These plaintext codes are shown only in this response.</p>
        <ul>
            @foreach ($recoveryCodes as $recoveryCode)
                <li><code>{{ $recoveryCode }}</code></li>
            @endforeach
        </ul>
        <p><a href="{{ route('identity.mfa.settings') }}">Return to MFA settings</a></p>
    </main>
</body>
</html>
