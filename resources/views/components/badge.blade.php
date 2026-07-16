@props(['variant' => 'gray', 'dotted' => true])

@php
    $variants = [
        'gray' => 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-300',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
        'green' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        'indigo' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300',
        'brand' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300',
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'navy' => 'bg-navy-50 text-navy-700 dark:bg-navy-500/20 dark:text-navy-200',
    ];

    $colors = [
        'gray' => 'bg-slate-400',
        'red' => 'bg-red-500',
        'green' => 'bg-emerald-500',
        'blue' => 'bg-blue-500',
        'indigo' => 'bg-brand-500',
        'brand' => 'bg-brand-500',
        'amber' => 'bg-amber-500',
        'navy' => 'bg-navy-500',
    ];

    $dotColor = $colors[$variant] ?? 'bg-slate-400';
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . ($variants[$variant] ?? $variants['gray'])]) }}>
    @if ($dotted)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
    @endif
    {{ $slot }}
</span>
