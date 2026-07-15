@props(['title' => null, 'wide' => false])

@php
    $home = auth()->user()?->isAdmin() ? 'admin.dashboard' : 'teacher.dashboard';
    $nav = [
        ['route' => $home, 'match' => $home, 'label' => 'Dashboard'],
        ['route' => 'schedule.index', 'match' => 'schedule.*', 'label' => 'My Schedule'],
        ['route' => 'attendance.index', 'match' => 'attendance.*', 'label' => 'Attendance'],
        ['route' => 'reports.sf2.index', 'match' => 'reports.sf2.*', 'label' => 'SF2 Report'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="h-full bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950 text-gray-800 dark:text-gray-100 antialiased">
<div x-data="{ mobileNav: false }" class="min-h-full">
    <header class="sticky top-0 z-20 border-b border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl shadow-sm">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6">
            <a href="{{ route($home) }}" class="flex shrink-0 items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto object-contain">
            </a>

            {{-- Desktop nav --}}
            <nav class="ml-2 hidden items-center gap-1 text-sm md:flex">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="rounded-lg px-3 py-2 font-medium transition-colors {{ request()->routeIs($item['match'])
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'
                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('portal') }}"
                   class="ml-1 inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-3.5 py-2 font-bold text-white hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                    Scan Portal
                </a>
            </nav>

            @if ($activeSchoolYear ?? null)
                <span class="ml-auto hidden lg:inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> SY {{ $activeSchoolYear->name }}
                </span>
            @endif

            {{-- User dropdown --}}
            <div x-data="{ open: false }" class="relative ml-auto lg:ml-2">
                <button @click="open = !open" class="group flex items-center gap-2 rounded-full py-1.5 pl-1.5 pr-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-xs font-bold text-white shadow-md">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="hidden text-sm font-medium sm:block">{{ auth()->user()->name }}</span>
                    <svg class="hidden sm:block h-4 w-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div x-show="open" x-cloak x-transition @click.outside="open = false"
                     class="absolute right-0 mt-2 w-48 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                    <div class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                        Signed in as<br><span class="font-semibold text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</span>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="flex w-full items-center gap-3 border-t border-gray-100 dark:border-gray-700 px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>

            {{-- Mobile hamburger --}}
            <button @click="mobileNav = !mobileNav" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 md:hidden transition-colors">
                <svg x-show="!mobileNav" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileNav" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Mobile nav panel --}}
        <nav x-show="mobileNav" x-cloak x-transition class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 md:hidden">
            <div class="flex flex-col gap-1 text-sm">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="rounded-lg px-4 py-2.5 font-medium transition-colors {{ request()->routeIs($item['match'])
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'
                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('portal') }}" class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-2.5 font-bold text-white">
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
            <div class="flex items-start gap-3 rounded-xl px-5 py-4 text-white shadow-xl {{ session('success') ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-red-600' }}">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    @if (session('success'))
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    @else
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    @endif
                </svg>
                <span class="flex-1 text-sm font-medium">{{ session('success') ?? session('error') }}</span>
                <button @click="show = false" class="text-white/80 hover:text-white transition-colors">&times;</button>
            </div>
        </div>
    @endif

    <main class="mx-auto {{ $wide ? 'max-w-full px-3 sm:px-5' : 'max-w-7xl px-4 sm:px-6' }} py-6">
        @if ($title)<h1 class="mb-5 text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>@endif
        {{ $slot }}
    </main>
</div>

@include('partials.confirm-delete-script')
</body>
</html>
