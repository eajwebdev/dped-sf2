@props(['variant' => 'primary', 'size' => 'md', 'disabled' => false, 'loading' => false])

@php
    $variants = [
        'primary' => 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white hover:shadow-lg hover:shadow-indigo-500/30 disabled:opacity-50',
        'secondary' => 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 disabled:opacity-50',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50',
        'outline' => 'border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm font-medium rounded-lg',
        'md' => 'px-6 py-2.5 text-sm font-bold rounded-lg',
        'lg' => 'px-8 py-3.5 text-base font-bold rounded-xl',
    ];

    $baseClasses = 'inline-flex items-center justify-center gap-2 transition-all duration-200 disabled:cursor-not-allowed';
@endphp

<button {{ $attributes->merge(['class' => "{$baseClasses} {$variants[$variant]} {$sizes[$size]}", 'disabled' => $disabled || $loading]) }}>
    @if ($loading)
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    {{ $slot }}
</button>
