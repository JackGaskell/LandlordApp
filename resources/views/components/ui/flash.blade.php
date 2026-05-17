@if (session('status'))
    <div {{ $attributes->merge(['class' => 'ui-alert-success mb-6']) }}>
        {{ session('status') }}
    </div>
@endif
