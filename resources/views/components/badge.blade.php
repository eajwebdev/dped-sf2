@props(['variant' => 'gray', 'dotted' => true])

@php
    $variants = [
        'gray' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 dot:bg-gray-500',
        'red' => 'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-300 dot:bg-red-500',
        'green' => 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 dot:bg-emerald-500',
        'blue' => 'bg-blue-100 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300 dot:bg-blue-500',
        'indigo' => 'bg-indigo-100 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 dot:bg-indigo-500',
        'amber' => 'bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 dot:bg-amber-500',
    ];

    $colors = [
        'gray' => 'bg-gray-500',
        'red' => 'bg-red-500',
        'green' => 'bg-emerald-500',
        'blue' => 'bg-blue-500',
        'indigo' => 'bg-indigo-500',
        'amber' => 'bg-amber-500',
    ];

    // Extract the dot color from variant
    $dotColor = $colors[$variant] ?? 'bg-gray-500';
@endphp

<span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold {{ $variants[$variant] }}">
    @if ($dotted)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
    @endif
    {{ $slot }}
</span>
