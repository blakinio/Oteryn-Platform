<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Oteryn Platform</title>
</head>
<body>
    <main>
        <h1>Change your Oteryn Platform password</h1>

        @if ($errors->any())
            <div role="alert">
                <p>The password could not be changed.</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('identity.password.change.update') }}">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password">Current password</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" maxlength="1024" required>
            </div>

            <div>
                <label for="password">New password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" maxlength="1024" required>
            </div>

            <div>
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" maxlength="1024" required>
            </div>

            <button type="submit">Change password</button>
        </form>
    </main>
</body>
</html>
