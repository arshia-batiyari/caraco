@extends('layouts.admin')

@section('content')
    <div class="k-admin-heading">
        <h1>{{ __('admin.users') }}</h1>
        <a class="k-btn k-btn-ink" href="{{ route('admin.users.create') }}">{{ __('admin.create') }}</a>
    </div>
    <div class="k-table-wrap">
        <table class="k-table">
            <thead><tr><th>{{ __('ui.fields.name') }}</th><th>{{ __('admin.role') }}</th></tr></thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td><a href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a></td>
                        <td>{{ __('admin.roles.'.$user->role) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
