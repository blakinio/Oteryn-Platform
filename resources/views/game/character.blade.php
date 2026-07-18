@extends('game.layout')

@section('title', $character->name)

@section('content')
    <h1>{{ $character->name }}</h1>

    <div class="card">
        <dl>
            <dt>Level</dt>
            <dd>{{ $character->level }}</dd>
            <dt>Vocation ID</dt>
            <dd>{{ $character->vocation }}</dd>
        </dl>
    </div>
@endsection
