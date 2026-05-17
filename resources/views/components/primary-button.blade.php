<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ui-btn-primary w-full sm:w-auto']) }}>
    {{ $slot }}
</button>
