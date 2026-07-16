@props(['variant' => 'primary', 'size' => 'md', 'disabled' => false, 'loading' => false])

@php
    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'danger' => 'btn-danger',
        'success' => 'btn bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-[0_4px_14px_-2px_rgb(34_197_94/0.35)] hover:from-emerald-600 hover:to-emerald-700',
        'outline' => 'btn-outline',
        'ghost' => 'btn-ghost',
    ];

    $sizes = [
        'sm' => 'btn-sm',
        'md' => 'btn-md',
        'lg' => 'btn-lg',
    ];
@endphp

<button {{ $attributes->merge(['class' => ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']), 'disabled' => $disabled || $loading]) }}>
    @if ($loading)
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    {{ $slot }}
</button>
