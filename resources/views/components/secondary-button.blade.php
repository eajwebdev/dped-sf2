<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-outline btn-md']) }}>
    {{ $slot }}
</button>
