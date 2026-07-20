@extends('admin.layout')

@section('title', 'Manage Administrator Roles')

@section('content')
    <h2>Administrator roles</h2>
    <p>Role changes are audited. The final platform administrator cannot be removed.</p>

    <table>
        <thead>
            <tr>
                <th>Identity</th>
                <th>Assigned roles</th>
                <th>Manage</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($identities as $identity)
                @php
                    $assignedRoles = $rolesByIdentity
                        ->get($identity->id, collect())
                        ->pluck('key')
                        ->all();
                @endphp
                <tr>
                    <td>{{ $identity->email }}</td>
                    <td>{{ $assignedRoles === [] ? 'None' : implode(', ', $assignedRoles) }}</td>
                    <td>
                        @foreach ($availableRoles as $role)
                            @if (in_array($role, $assignedRoles, true))
                                <form method="POST" action="{{ route('admin.roles.destroy', ['identity' => $identity, 'roleKey' => $role]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Remove {{ $role }}</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.roles.store', $identity) }}">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ $role }}">
                                    <button type="submit">Assign {{ $role }}</button>
                                </form>
                            @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $identities->links() }}
@endsection
