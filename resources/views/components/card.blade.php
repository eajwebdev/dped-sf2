@props(['title' => null, 'actions' => null, 'hover' => false, 'glass' => false, 'accent' => false, 'padding' => true])

<div {{ $attributes->merge(['class' =>
    ($glass ? 'card-glass' : 'card')
    . ($hover ? ' card-hover' : '')
    . ($accent ? ' relative overflow-hidden before:absolute before:inset-x-0 before:top-0 before:h-[3px] before:bg-gradient-to-r before:from-brand-500 before:via-brand-400 before:to-navy-500' : '')
]) }}>
    @if ($title || $actions)
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/10">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h2>
            @if ($actions)<div class="flex items-center gap-2">{{ $actions }}</div>@endif
        </div>
    @endif
    <div class="{{ $padding ? 'p-6' : '' }}">
        {{ $slot }}
    </div>
</div>
