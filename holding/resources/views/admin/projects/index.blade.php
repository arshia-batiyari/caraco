@extends('layouts.admin')

@section('title', __('admin.projects'))

@section('content')
    <div class="k-admin-heading">
        <h1>{{ __('admin.projects') }}</h1>
        <a class="k-btn k-btn-ink" href="{{ route('admin.projects.create') }}">{{ __('admin.create') }}</a>
    </div>
    @if ($projects->isEmpty())
        <p>{{ __('admin.empty') }}</p>
    @else
        <div class="k-table-wrap">
            <table class="k-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.position') }}</th>
                        <th>{{ __('admin.title') }}</th>
                        <th>{{ __('admin.published') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td>{{ $project->position }}</td>
                            <td>{{ $project->translated()?->title }}</td>
                            <td>{{ $project->is_published ? __('admin.yes') : __('admin.no') }}</td>
                            <td><a href="{{ route('admin.projects.edit', $project) }}">{{ __('admin.edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
