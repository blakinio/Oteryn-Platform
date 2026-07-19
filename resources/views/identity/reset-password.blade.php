<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Oteryn Platform</title>
</head>
<body>
    <main>
        <h1>Choose a new Oteryn Platform password</h1>

        @if ($errors->any())
            <div role="alert">
                <p>The password could not be reset.</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input name="token" type="hidden" value="{{ $token }}">

            <div>
                <label for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $email) }}"
                    autocomplete="email"
                    maxlength="254"
                    required
                >
            </div>

            <div>
                <label for="password">New password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    maxlength="1024"
                    required
                >
            </div>

            <div>
                <label for="password_confirmation">Confirm new password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    maxlength="1024"
                    required
                >
            </div>

            <button type="submit">Reset password</button>
        </form>
    </main>
</body>
</html>
