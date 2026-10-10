@extends('layouts.admin')

@section('title', __('admin.pages'))

@section('content')
    <div class="k-admin-heading">
        <h1>{{ __('admin.pages') }}</h1>
        <a class="k-btn k-btn-ink" href="{{ route('admin.pages.create') }}">{{ __('admin.create') }}</a>
    </div>
    <div class="k-table-wrap">
        <table class="k-table">
            <thead>
                <tr>
                    <th>{{ __('admin.title') }}</th>
                    <th>{{ __('admin.published') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pages as $page)
                    <tr>
                        <td>{{ $page->translated()?->title ?: ($page->key ?: '—') }}</td>
                        <td>{{ $page->is_published ? __('admin.yes') : __('admin.no') }}</td>
                        <td><a href="{{ route('admin.pages.edit', $page) }}">{{ __('admin.edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
