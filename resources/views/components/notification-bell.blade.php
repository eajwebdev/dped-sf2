@php
    $notifier = app(\App\Services\NotificationService::class);
    $user = auth()->user();
    $items = $user ? $notifier->for($user) : [];
    $count = count($items);
    $level = $user ? $notifier->highestLevel($user) : null;

    $dot = match ($level) {
        'danger' => 'bg-red-500',
        'warning' => 'bg-amber-500',
        default => 'bg-brand-500',
    };
@endphp

<div x-data="{ open: false }" class="relative">
    <button type="button" @click="open = !open" @keydown.escape.window="open = false"
            class="relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/10"
            :aria-expanded="open"
            aria-label="{{ $count ? $count.' notification'.($count === 1 ? '' : 's') : 'Notifications' }}">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
        </svg>

        @if ($count)
            {{-- Count badge, with a soft ping so an expiring subscription is noticeable --}}
            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full px-1 text-[10px] font-bold text-white {{ $dot }}">
                {{ $count > 9 ? '9+' : $count }}
            </span>
            @if ($level === 'danger')
                <span class="absolute -right-0.5 -top-0.5 h-4 w-4 animate-ping rounded-full {{ $dot }} opacity-60"></span>
            @endif
        @endif
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute right-0 z-40 mt-2 w-80 origin-top-right overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lift dark:border-white/10 dark:bg-navy-800">

        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-2.5 dark:border-white/10 dark:bg-white/5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Notifications</span>
            @if ($count)
                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $count }}</span>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($items as $item)
                @php
                    $accent = match ($item['level']) {
                        'danger' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300',
                        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
                        default => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300',
                    };
                @endphp
                <div class="flex gap-3 border-b border-slate-100 px-4 py-3 last:border-0 dark:border-white/5">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $accent }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            @if ($item['level'] === 'info')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @endif
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item['title'] }}</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $item['body'] }}</p>
                        @if ($item['url'])
                            <a href="{{ $item['url'] }}" class="mt-1.5 inline-block text-xs font-semibold text-brand-600 transition-colors hover:text-brand-500 dark:text-brand-300">
                                {{ $item['cta'] }} &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-8 w-8 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300">You're all caught up</p>
                    <p class="mt-0.5 text-xs text-slate-400">We'll let you know before your access expires.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
