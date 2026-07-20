<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Character | Oteryn Platform</title>
</head>
<body>
    <main>
        <h1>Create a character</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div role="alert">
                <p>The character could not be created.</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('account.characters.store') }}">
            @csrf

            <div>
                <label for="name">Character name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="255" autocomplete="off" required>
                <p>Use 1-3 words containing ASCII letters only.</p>
            </div>

            <div>
                <label for="vocation">Vocation</label>
                <select id="vocation" name="vocation" required>
                    <option value="1" @selected((string) old('vocation') === '1')>Sorcerer</option>
                    <option value="2" @selected((string) old('vocation') === '2')>Druid</option>
                    <option value="3" @selected((string) old('vocation') === '3')>Paladin</option>
                    <option value="4" @selected((string) old('vocation') === '4')>Knight</option>
                    <option value="9" @selected((string) old('vocation') === '9')>Monk</option>
                </select>
            </div>

            <div>
                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="0" @selected((string) old('sex') === '0')>0</option>
                    <option value="1" @selected((string) old('sex', '1') === '1')>1</option>
                </select>
            </div>

            <button type="submit">Create character</button>
        </form>
    </main>
</body>
</html>
