<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    @stack('head')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brand-art.css') }}">
    <link rel="stylesheet" href="{{ asset('css/public-shell.css') }}">
    @stack('styles')
</head>
<body class="public-body">
@inject('publicNavigation', 'App\PublicPortal\Navigation\PublicNavigationRegistry')
<a class="skip-link" href="#main-content">Skip to content</a>
@include('game.partials.public-header', ['headerItems' => $publicNavigation->header()])
<main id="main-content" class="page-shell @yield('page-class')">
    @yield('content')
</main>
@include('game.partials.public-footer', ['footerGroups' => $publicNavigation->footer()])
</body>
</html>
