@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'ui-alert-success']) }}>
        {{ $status }}
    </div>
@endif
