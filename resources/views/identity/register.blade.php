<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Oteryn Platform</title>
</head>
<body>
    <main>
        <h1>Create an Oteryn Platform identity</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div role="alert">
                <p>Registration could not be completed.</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('identity.register.store') }}">
            @csrf

            <div>
                <label for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    maxlength="254"
                    required
                >
            </div>

            <div>
                <label for="password">Password</label>
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
                <label for="password_confirmation">Confirm password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    maxlength="1024"
                    required
                >
            </div>

            <button type="submit">Register</button>
        </form>
    </main>
</body>
</html>
