@extends('identity.layout')

@section('title', 'Change Password')
@section('error-title', 'The password could not be changed.')

@section('content')
    <div class="page-header">
        <p class="eyebrow">Account security</p>
        <h1>Change your Oteryn Platform password</h1>
        <p class="muted">Changing your password revokes Platform web sessions. You will need to sign in again afterward.</p>
    </div>

    <form class="form-stack" method="POST" action="{{ route('identity.password.change.update') }}">
        @csrf
        @method('PUT')
        <div class="form-field">
            <label for="current_password">Current password</label>
            <input id="current_password" name="current_password" type="password" autocomplete="current-password" maxlength="1024" required autofocus>
        </div>
        <div class="form-field">
            <label for="password">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" maxlength="1024" required>
        </div>
        <div class="form-field">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" maxlength="1024" required>
        </div>
        <button type="submit">Change password</button>
    </form>
@endsection
