@props([
    'title' => 'Nothing here yet',
    'description' => null,
    'icon' => 'M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776',
])

<div {{ $attributes->merge(['class' => 'animate-fade-in flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <span class="flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 dark:bg-white/5">
        <svg class="h-7 w-7 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
        </svg>
    </span>
    <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
    @if (trim($slot))
        <div class="mt-5 flex items-center gap-3">{{ $slot }}</div>
    @endif
</div>
