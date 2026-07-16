@props(['variant' => 'info', 'title' => null, 'dismissible' => false])

@php
    $styles = [
        'success' => ['wrap' => 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/25 dark:bg-emerald-500/10', 'icon' => 'text-emerald-500', 'text' => 'text-emerald-800 dark:text-emerald-200', 'path' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'error' => ['wrap' => 'border-red-200 bg-red-50 dark:border-red-500/25 dark:bg-red-500/10', 'icon' => 'text-red-500', 'text' => 'text-red-800 dark:text-red-200', 'path' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z'],
        'warning' => ['wrap' => 'border-amber-200 bg-amber-50 dark:border-amber-500/25 dark:bg-amber-500/10', 'icon' => 'text-amber-500', 'text' => 'text-amber-800 dark:text-amber-200', 'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
        'info' => ['wrap' => 'border-blue-200 bg-blue-50 dark:border-blue-500/25 dark:bg-blue-500/10', 'icon' => 'text-blue-500', 'text' => 'text-blue-800 dark:text-blue-200', 'path' => 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z'],
    ];
    $s = $styles[$variant] ?? $styles['info'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.200ms
     {{ $attributes->merge(['class' => "animate-slide-up flex items-start gap-3 rounded-2xl border p-4 {$s['wrap']}"]) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $s['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['path'] }}"/>
    </svg>
    <div class="min-w-0 flex-1 text-sm {{ $s['text'] }}">
        @if ($title)<p class="font-bold">{{ $title }}</p>@endif
        <div class="{{ $title ? 'mt-0.5' : '' }}">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" @click="show = false" class="shrink-0 cursor-pointer opacity-60 transition-opacity hover:opacity-100 {{ $s['text'] }}" aria-label="Dismiss">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
