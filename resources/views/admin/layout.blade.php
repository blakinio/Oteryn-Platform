<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Oteryn Admin')</title>
</head>
<body>
    <header>
        <h1><a href="{{ route('admin.dashboard') }}">Oteryn Admin</a></h1>
        <nav aria-label="Administrator navigation">
            <a href="{{ route('admin.news.index') }}">News</a>
            <a href="{{ route('admin.pages.index') }}">Pages</a>
            <a href="{{ route('admin.roles.index') }}">Roles</a>
            <a href="{{ route('admin.audit.index') }}">Audit</a>
            <a href="{{ route('home') }}">Public site</a>
        </nav>
    </header>

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

    <main>
        @yield('content')
    </main>
</body>
</html>
