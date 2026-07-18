@props(['title' => null, 'wide' => false])

@php
    $isTeacher = auth()->user()?->isTeacher();
    $home = auth()->user()?->isAdmin() ? 'admin.dashboard' : 'teacher.dashboard';
    $nav = array_values(array_filter([
        ['route' => $home, 'match' => $home, 'label' => 'Dashboard'],
        ['route' => 'schedule.index', 'match' => 'schedule.*', 'label' => 'My Schedule'],
        $isTeacher ? ['route' => 'teacher.students.index', 'match' => 'teacher.students.*', 'label' => 'Students'] : null,
        $isTeacher ? ['route' => 'teacher.subjects.index', 'match' => 'teacher.subjects.*', 'label' => 'Subjects'] : null,
        ['route' => 'attendance.index', 'match' => 'attendance.*', 'label' => 'Attendance'],
    ]));

    // Reports live in their own dropdown so new School Forms can be added
    // without pushing the rest of the nav off the bar.
    $reports = [
        ['route' => 'reports.sf1.index', 'match' => 'reports.sf1.*', 'label' => 'SF1 — School Register', 'desc' => 'Class master list'],
        ['route' => 'reports.sf2.index', 'match' => 'reports.sf2.*', 'label' => 'SF2 — Daily Attendance', 'desc' => 'Monthly attendance report'],
    ];
    $reportsActive = collect($reports)->contains(fn ($r) => request()->routeIs($r['match']));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('eaj-appicon.png') }}">
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased dark:bg-navy-900 dark:text-slate-100">
<div x-data="{ mobileNav: false }" class="min-h-full">
    <header class="relative sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur-2xl dark:border-white/10 dark:bg-navy-900/80">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6">
            {{-- Logo only: the mark carries the brand, so the name is on the label instead. --}}
            <a href="{{ route($home) }}" class="flex shrink-0 items-center" aria-label="{{ config('app.name') }} — Home">
                <img src="{{ asset('eaj-appicon.png') }}" alt="" class="h-9 w-9 rounded-xl object-contain">
            </a>

            {{-- Desktop nav: full links need ~1024px; below that the hamburger takes over --}}
            <nav class="ml-2 hidden shrink-0 items-center gap-1 text-sm lg:flex">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="relative whitespace-nowrap rounded-xl px-3 py-2 font-medium transition-all duration-200 {{ request()->routeIs($item['match'])
                            ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300'
                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' }}">
                        {{ $item['label'] }}
                        @if (request()->routeIs($item['match']))
                            <span class="absolute inset-x-3 -bottom-[13px] h-0.5 rounded-full bg-brand-500"></span>
                        @endif
                    </a>
                @endforeach

                {{-- Reports dropdown: opens on hover, and on click for keyboard/touch --}}
                <div class="relative" x-data="{ open: false }"
                     @mouseenter="open = true" @mouseleave="open = false"
                     @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open"
                            :aria-expanded="open" aria-haspopup="true"
                            class="relative flex cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-xl px-3 py-2 font-medium transition-all duration-200 {{ $reportsActive
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' }}">
                        Reports
                        <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                             fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        @if ($reportsActive)
                            <span class="absolute inset-x-3 -bottom-[13px] h-0.5 rounded-full bg-brand-500"></span>
                        @endif
                    </button>

                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="absolute left-0 top-full z-30 w-72 pt-2">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lift dark:border-white/10 dark:bg-navy-800">
                            <p class="border-b border-slate-100 bg-slate-50 px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                                DepEd School Forms
                            </p>
                            @foreach ($reports as $r)
                                <a href="{{ route($r['route']) }}"
                                   class="flex items-start gap-3 px-4 py-3 transition-colors {{ request()->routeIs($r['match'])
                                        ? 'bg-brand-50 dark:bg-brand-500/10'
                                        : 'hover:bg-slate-50 dark:hover:bg-white/5' }}">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $r['label'] }}</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $r['desc'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                            <p class="border-t border-slate-100 px-4 py-2.5 text-[11px] text-slate-400 dark:border-white/10">
                                More School Forms coming soon.
                            </p>
                        </div>
                    </div>
                </div>
            </nav>

            @if ($activeSchoolYear ?? null)
                <span class="ml-auto hidden items-center gap-1.5 whitespace-nowrap rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 lg:inline-flex">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    SY {{ $activeSchoolYear->name }}
                </span>
            @endif

            {{-- Trial / subscription status (teachers only): blinking icon, details on hover --}}
            @php
                $subState = auth()->user()?->isTeacher() ? auth()->user()->subscriptionState() : null;
                $isTrial = $subState === 'trial';
                $trialEnds = auth()->user()?->trial_ends_at;
            @endphp
            @if ($subState === 'trial' || $subState === 'expired')
                <div class="{{ ($activeSchoolYear ?? null) ? '' : 'ml-auto' }} relative shrink-0"
                     x-data="{ hover: false }"
                     @mouseenter="hover = true"
                     @mouseleave="hover = false">
                    <a href="{{ route('subscribe.show') }}"
                       @focus="hover = true"
                       @blur="hover = false"
                       aria-label="{{ $isTrial ? 'Free trial'.($trialEnds ? ' ends '.$trialEnds->format('M d, Y') : '') : 'Trial ended — subscribe' }}"
                       class="relative flex h-9 w-9 items-center justify-center rounded-full transition-colors {{ $isTrial
                            ? 'bg-brand-50 text-brand-600 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300'
                            : 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300' }}">
                        {{-- blinking halo --}}
                        <span class="absolute inline-flex h-2.5 w-2.5 animate-ping rounded-full opacity-75 {{ $isTrial ? 'bg-brand-500' : 'bg-red-500' }}"></span>
                        <svg class="relative h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            @if ($isTrial)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.34 3.94L2.7 16.13c-.87 1.5.21 3.37 1.94 3.37h14.72c1.73 0 2.81-1.87 1.94-3.37L13.66 3.94c-.87-1.5-3.03-1.5-3.9 0z"/>
                            @endif
                        </svg>
                    </a>

                    <div x-show="hover" x-cloak role="tooltip"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute left-1/2 top-full z-30 mt-2 w-60 -translate-x-1/2 rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-lift dark:border-white/10 dark:bg-navy-800">
                        @if ($isTrial)
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Free trial</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                @if ($trialEnds)
                                    Ends {{ $trialEnds->format('M d, Y') }} ({{ $trialEnds->diffForHumans(['parts' => 1]) }}).
                                @endif
                                Subscribe to keep your classes and SF2 reports after it ends.
                            </p>
                            <p class="mt-2 text-xs font-semibold text-brand-600 dark:text-brand-300">Click to subscribe →</p>
                        @else
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Trial ended</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                Your free trial is over. Subscribe to restore access to your classes and SF2 reports.
                            </p>
                            <p class="mt-2 text-xs font-semibold text-red-600 dark:text-red-300">Click to subscribe →</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="ml-auto flex items-center gap-1 lg:ml-2">
                <x-theme-toggle />

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="group flex cursor-pointer items-center gap-2 rounded-full py-1.5 pl-1.5 pr-1.5 transition-colors hover:bg-slate-100 dark:hover:bg-white/10 xl:pr-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white shadow-glow-pink-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="hidden whitespace-nowrap text-sm font-medium xl:block">{{ auth()->user()->name }}</span>
                        <svg class="hidden h-4 w-4 text-slate-400 transition-colors group-hover:text-slate-600 dark:group-hover:text-slate-300 xl:block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-52 origin-top-right overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lift dark:border-white/10 dark:bg-navy-800">
                        <div class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-xs text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                            Signed in as<br><span class="font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</span>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-slate-50 dark:hover:bg-white/5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profile Settings
                        </a>
                        @if (auth()->user()?->isTeacher())
                            <a href="{{ route('subscribe.show') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-slate-50 dark:hover:bg-white/5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Subscription
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="flex w-full cursor-pointer items-center gap-3 border-t border-slate-100 px-4 py-2.5 text-left text-sm text-red-600 transition-colors hover:bg-red-50 dark:border-white/10 dark:hover:bg-red-500/10">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Mobile hamburger --}}
                <button @click="mobileNav = !mobileNav" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/10 lg:hidden" aria-label="Menu">
                    <svg x-show="!mobileNav" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileNav" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile nav: absolutely positioned so opening it overlays the page
             instead of growing the sticky header and pushing content down. --}}
        <div x-show="mobileNav" x-cloak @click="mobileNav = false"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-x-0 bottom-0 top-16 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

        <nav x-show="mobileNav" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
             class="absolute inset-x-0 top-full z-40 max-h-[calc(100vh-4rem)] overflow-y-auto border-t border-slate-200 bg-white px-4 py-3 shadow-lift dark:border-white/10 dark:bg-navy-900 lg:hidden">
            <div class="flex flex-col gap-1 text-sm">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="rounded-xl px-4 py-3 font-medium transition-colors {{ request()->routeIs($item['match'])
                            ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300'
                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach

                {{-- Reports group: collapsed by default, open when you are inside one --}}
                <div x-data="{ reportsOpen: {{ $reportsActive ? 'true' : 'false' }} }" class="mt-1">
                    <button type="button" @click="reportsOpen = !reportsOpen"
                            :aria-expanded="reportsOpen"
                            class="flex w-full cursor-pointer items-center justify-between rounded-xl px-4 py-3 font-medium transition-colors {{ $reportsActive
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' }}">
                        Reports
                        <svg class="h-4 w-4 transition-transform duration-200" :class="reportsOpen ? 'rotate-180' : ''"
                             fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="reportsOpen" x-collapse x-cloak>
                        <div class="mt-1 space-y-1 border-l-2 border-slate-200 pl-3 dark:border-white/10">
                            @foreach ($reports as $r)
                                <a href="{{ route($r['route']) }}"
                                   class="block rounded-xl px-4 py-2.5 transition-colors {{ request()->routeIs($r['match'])
                                        ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300'
                                        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' }}">
                                    <span class="block text-sm font-semibold">{{ $r['label'] }}</span>
                                    <span class="block text-xs text-slate-400">{{ $r['desc'] }}</span>
                                </a>
                            @endforeach
                            <p class="px-4 py-2 text-[11px] text-slate-400">More School Forms coming soon.</p>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    {{-- Flash toasts --}}
    @if (session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-cloak
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed right-4 top-20 z-50 max-w-sm">
            <div class="flex items-start gap-3 rounded-2xl px-5 py-4 text-white shadow-lift {{ session('success') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-red-600' }}">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    @if (session('success'))
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    @else
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    @endif
                </svg>
                <span class="flex-1 text-sm font-medium">{{ session('success') ?? session('error') }}</span>
                <button @click="show = false" class="cursor-pointer text-white/80 transition-colors hover:text-white" aria-label="Dismiss">&times;</button>
            </div>
        </div>
    @endif

    <main class="mx-auto {{ $wide ? 'max-w-full px-3 sm:px-5' : 'max-w-7xl px-4 sm:px-6' }} animate-fade-in py-6">
        @if ($title)<h1 class="mb-5 text-xl font-bold text-slate-900 dark:text-white">{{ $title }}</h1>@endif
        {{ $slot }}
    </main>
</div>

@include('partials.confirm-delete-script')
</body>
</html>
