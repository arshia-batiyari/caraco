@props(['type' => 'info'])

<div {{ $attributes->class(['k-alert', 'k-alert-'.$type]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    {{ $slot }}
</div>
