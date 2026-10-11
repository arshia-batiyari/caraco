@extends('layouts.admin')

@section('title', __('admin.users'))

@section('content')
    <div class="k-admin-heading">
        <h1>{{ __('admin.users') }}</h1>
        <a class="k-btn k-btn-ink" href="{{ route('admin.users.create') }}">{{ __('admin.create') }}</a>
    </div>
    @include('admin.partials.filters', ['showPublished' => false])
    @if ($users->isEmpty())
        <x-empty-state :title="__('admin.empty')" />
    @else
        <div class="k-table-wrap">
            <table class="k-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.fields.name') }}</th>
                        <th>{{ __('ui.fields.email') }}</th>
                        <th>{{ __('admin.role') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $account)
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td>{{ $account->email }}</td>
                            <td>{{ __('admin.roles.'.$account->role) }}</td>
                            <td><a href="{{ route('admin.users.edit', $account) }}">{{ __('admin.edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    @endif
@endsection
