@extends('layouts.site')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => $heading],
    ]" />
    <x-division-list :divisions="$divisions" :heading="$heading" level="1" />
@endsection
