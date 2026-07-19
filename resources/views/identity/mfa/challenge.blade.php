<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA challenge | Oteryn Platform</title>
</head>
<body>
    <main>
        <h1>Complete your sign in</h1>
        <p>Enter a fresh six-digit authenticator code or one unused recovery code.</p>

        @if ($errors->any())
            <div role="alert">
                <p>Sign in could not be completed.</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('identity.mfa.challenge.store') }}">
            @csrf
            <div>
                <label for="code">Authenticator or recovery code</label>
                <input id="code" name="code" type="text" autocomplete="one-time-code" maxlength="64" required autofocus>
            </div>
            <button type="submit">Verify and sign in</button>
        </form>
    </main>
</body>
</html>
