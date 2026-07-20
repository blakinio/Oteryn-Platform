@extends('admin.layout')

@section('title', 'Administrator Audit')

@section('content')
    <h2>Administrator audit</h2>

    <table>
        <thead>
            <tr>
                <th>Occurred</th>
                <th>Actor</th>
                <th>Action</th>
                <th>Target</th>
                <th>Metadata</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($events as $event)
                <tr>
                    <td>{{ $event->occurred_at }}</td>
                    <td>{{ $event->actor_email ?? 'bootstrap/system' }}</td>
                    <td>{{ $event->action }}</td>
                    <td>{{ $event->target_type }}{{ $event->target_id === null ? '' : ':'.$event->target_id }}</td>
                    <td>{{ $event->metadata ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $events->links() }}
@endsection
