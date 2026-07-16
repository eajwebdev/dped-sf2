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
        ['route' => 'reports.sf2.index', 'match' => 'reports.sf2.*', 'label' => 'SF2 Report'],
    ]));
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
    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur-2xl dark:border-white/10 dark:bg-navy-900/80">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6">
            <a href="{{ route($home) }}" class="flex shrink-0 items-center gap-2.5">
                <img src="{{ asset('eaj-appicon.png') }}" alt="" class="h-9 w-9 rounded-xl object-contain">
                <span class="hidden text-base font-extrabold tracking-tight text-slate-900 dark:text-white sm:block">{{ config('app.name') }}</span>
            </a>

            {{-- Desktop nav --}}
            <nav class="ml-2 hidden items-center gap-1 text-sm md:flex">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="relative rounded-xl px-3.5 py-2 font-medium transition-all duration-200 {{ request()->routeIs($item['match'])
                            ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300'
                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' }}">
                        {{ $item['label'] }}
                        @if (request()->routeIs($item['match']))
                            <span class="absolute inset-x-3 -bottom-[13px] h-0.5 rounded-full bg-brand-500"></span>
                        @endif
                    </a>
                @endforeach
                <a href="{{ route('portal') }}"
                   class="btn-primary btn-sm ml-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                    Scan Portal
                </a>
            </nav>

            @if ($activeSchoolYear ?? null)
                <span class="ml-auto hidden items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 lg:inline-flex">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    SY {{ $activeSchoolYear->name }}
                </span>
            @endif

            {{-- Trial / subscription pill (teachers only) --}}
            @php $subState = auth()->user()?->isTeacher() ? auth()->user()->subscriptionState() : null; @endphp
            @if ($subState === 'trial')
                <a href="{{ route('subscribe.show') }}" class="{{ ($activeSchoolYear ?? null) ? '' : 'ml-auto' }} hidden items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-600 transition-colors hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300 lg:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span> Trial ends {{ auth()->user()->trial_ends_at->diffForHumans(['parts' => 1]) }}
                </a>
            @elseif ($subState === 'expired')
                <a href="{{ route('subscribe.show') }}" class="{{ ($activeSchoolYear ?? null) ? '' : 'ml-auto' }} hidden items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 transition-colors hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300 lg:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Subscribe
                </a>
            @endif

            <div class="ml-auto flex items-center gap-1 lg:ml-2">
                <x-theme-toggle />

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="group flex cursor-pointer items-center gap-2 rounded-full py-1.5 pl-1.5 pr-1.5 transition-colors hover:bg-slate-100 dark:hover:bg-white/10 sm:pr-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white shadow-glow-pink-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="hidden text-sm font-medium sm:block">{{ auth()->user()->name }}</span>
                        <svg class="hidden h-4 w-4 text-slate-400 transition-colors group-hover:text-slate-600 dark:group-hover:text-slate-300 sm:block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
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
                <button @click="mobileNav = !mobileNav" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/10 md:hidden" aria-label="Menu">
                    <svg x-show="!mobileNav" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileNav" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile nav panel --}}
        <nav x-show="mobileNav" x-cloak x-transition class="border-t border-slate-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-navy-900 md:hidden">
            <div class="flex flex-col gap-1 text-sm">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="rounded-xl px-4 py-3 font-medium transition-colors {{ request()->routeIs($item['match'])
                            ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300'
                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('portal') }}" class="btn-primary btn-md mt-1">
                    Scan Portal
                </a>
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
