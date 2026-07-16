@props([
    'label',
    'value',
    'icon' => null,          // SVG path d="" string (24x24 stroke icon)
    'tone' => 'brand',       // brand | navy | success | warning | danger | info
    'trend' => null,         // e.g. "+12%"
    'trendUp' => true,
    'href' => null,
    'animate' => true,       // count-up animation for numeric values
])

@php
    $tones = [
        'brand' => 'from-brand-500 to-brand-600 shadow-glow-pink-sm',
        'navy' => 'from-navy-700 to-navy-900 shadow-[0_4px_14px_-2px_rgb(9_20_61/0.4)]',
        'success' => 'from-emerald-500 to-emerald-600 shadow-[0_4px_14px_-2px_rgb(34_197_94/0.35)]',
        'warning' => 'from-amber-500 to-amber-600 shadow-[0_4px_14px_-2px_rgb(245_158_11/0.35)]',
        'danger' => 'from-red-500 to-red-600 shadow-[0_4px_14px_-2px_rgb(239_68_68/0.35)]',
        'info' => 'from-blue-500 to-blue-600 shadow-[0_4px_14px_-2px_rgb(59_130_246/0.35)]',
    ];
    $toneClass = $tones[$tone] ?? $tones['brand'];
    $numeric = $animate && is_numeric(str_replace([',', ' '], '', (string) $value));
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'card card-hover group relative overflow-hidden p-5 ' . ($href ? 'cursor-pointer' : '')]) }}>
    <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-gradient-to-br {{ $toneClass }} opacity-[0.07] transition-transform duration-500 group-hover:scale-150"></div>

    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $label }}</p>

            @if ($numeric)
                <p class="mt-2 text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white"
                   x-data="{ n: 0, target: {{ (float) str_replace([',', ' '], '', (string) $value) }} }"
                   x-init="if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { n = target } else {
                               const t0 = performance.now(), dur = 900;
                               const tick = (t) => { const p = Math.min((t - t0) / dur, 1); n = Math.round(target * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(tick) };
                               requestAnimationFrame(tick);
                           }"
                   x-text="n.toLocaleString()">{{ $value }}</p>
            @else
                <p class="mt-2 truncate text-3xl font-extrabold text-slate-900 dark:text-white">{{ $value }}</p>
            @endif

            @if ($trend)
                <p class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold {{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                    <svg class="h-3.5 w-3.5 {{ $trendUp ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/>
                    </svg>
                    {{ $trend }}
                </p>
            @endif
        </div>

        @if ($icon)
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br text-white {{ $toneClass }} transition-transform duration-300 group-hover:scale-110">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </span>
        @endif
    </div>

    {{ $slot }}
</{{ $tag }}>
