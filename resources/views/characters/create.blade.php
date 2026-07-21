@extends('identity.layout')

@section('title', 'Create Character')
@section('error-title', 'The character could not be created.')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Account character</p>
        <h1>Create a character</h1>
        <p class="muted">Create a new character for the game account bound to your authenticated Platform identity.</p>
    </div>

    <form class="form-stack" method="POST" action="{{ route('account.characters.store') }}">
        @csrf
        <div class="form-field">
            <label for="name">Character name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="255" autocomplete="off" required autofocus>
            <p class="form-help">Use 1–3 words containing ASCII letters only.</p>
        </div>
        <div class="form-field">
            <label for="vocation">Vocation</label>
            <select id="vocation" name="vocation" required>
                <option value="1" @selected((string) old('vocation') === '1')>Sorcerer</option>
                <option value="2" @selected((string) old('vocation') === '2')>Druid</option>
                <option value="3" @selected((string) old('vocation') === '3')>Paladin</option>
                <option value="4" @selected((string) old('vocation') === '4')>Knight</option>
                <option value="9" @selected((string) old('vocation') === '9')>Monk</option>
            </select>
        </div>
        <div class="form-field">
            <label for="sex">Sex</label>
            <select id="sex" name="sex" required>
                <option value="0" @selected((string) old('sex') === '0')>Female</option>
                <option value="1" @selected((string) old('sex', '1') === '1')>Male</option>
            </select>
        </div>
        <button type="submit">Create character</button>
    </form>

    <div class="identity-links">
        <a href="{{ route('identity.mfa.settings') }}">Account security</a>
        <a href="{{ route('home') }}">Return to the public site</a>
    </div>
@endsection
