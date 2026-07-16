@props(['title' => null, 'breadcrumbs' => null])

@php
    $nav = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'admin.schools.index', 'label' => 'Schools', 'icon' => 'building'],
        ['route' => 'admin.registrations.index', 'label' => 'Registrations', 'icon' => 'inbox', 'badge' => $pendingRegistrations ?? 0],
        ['route' => 'admin.school-years.index', 'label' => 'School Years', 'icon' => 'calendar'],
        ['route' => 'admin.grade-levels.index', 'label' => 'Grade Levels', 'icon' => 'layers'],
        ['route' => 'admin.sections.index', 'label' => 'Sections', 'icon' => 'grid'],
        ['route' => 'admin.subjects.index', 'label' => 'Subjects', 'icon' => 'book'],
        ['route' => 'admin.teachers.index', 'label' => 'Teachers', 'icon' => 'users'],
        ['route' => 'admin.students.index', 'label' => 'Students', 'icon' => 'id'],
        ['route' => 'admin.enrollments.index', 'label' => 'Enrollment', 'icon' => 'clipboard'],
        ['route' => 'attendance.index', 'label' => 'Attendance', 'icon' => 'check'],
        ['route' => 'reports.sf2.index', 'label' => 'SF2 Report', 'icon' => 'report'],
        ['route' => 'admin.promotion.index', 'label' => 'Promotion', 'icon' => 'up'],
        ['route' => 'admin.audit-logs.index', 'label' => 'Audit Logs', 'icon' => 'shield'],
    ];
    $icons = [
        'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'building' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m4-4h.01M7 13h.01M7 9h.01M11 17h.01M11 13h.01M11 9h.01M15 17h.01M15 13h.01M15 9h.01',
        'inbox' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
        'layers' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        'grid' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
        'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'users' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-3-3',
        'id' => 'M15 9h3m-3 3h3m-6 4h6a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2h6zm-3-9a2 2 0 11-4 0 2 2 0 014 0zM7 16a3 3 0 016 0',
        'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-6 4h6',
        'check' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'report' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'up' => 'M5 10l7-7m0 0l7 7m-7-7v18',
        'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    ];
    $pending = (int) ($pendingRegistrations ?? 0);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('eaj-appicon.png') }}">
    @include('partials.theme-script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased dark:bg-navy-900 dark:text-slate-100">
<div x-data="{
        sidebarOpen: false,
        collapsed: localStorage.getItem('eaj-sidebar') === '1',
        toggleCollapse() { this.collapsed = !this.collapsed; localStorage.setItem('eaj-sidebar', this.collapsed ? '1' : '0') }
     }"
     class="min-h-full">

    {{-- Mobile drawer backdrop --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-30 bg-navy-900/50 backdrop-blur-sm lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', collapsed ? 'lg:w-[76px]' : 'lg:w-64']"
           class="fixed inset-y-0 left-0 z-40 flex w-72 transform flex-col border-r border-slate-200/80 bg-white/80 backdrop-blur-2xl transition-all duration-300 dark:border-white/10 dark:bg-navy-900/80 lg:w-64">

        {{-- Logo --}}
        <div class="flex h-16 shrink-0 items-center border-b border-slate-200/80 px-4 dark:border-white/10"
             :class="collapsed ? 'lg:justify-center lg:px-2' : ''">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 overflow-hidden">
                <img src="{{ asset('eaj-appicon.png') }}" alt="" class="h-9 w-9 shrink-0 rounded-xl object-contain">
                <span class="truncate text-base font-extrabold tracking-tight text-slate-900 transition-all duration-200 dark:text-white"
                      :class="collapsed ? 'lg:hidden' : ''">{{ config('app.name') }}</span>
            </a>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 space-y-1 overflow-y-auto p-3">
            @foreach ($nav as $item)
                @php $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
                <a href="{{ route($item['route']) }}"
                   @if($active) aria-current="page" @endif
                   class="side-link group {{ $active
                        ? 'bg-gradient-to-r from-brand-500 to-brand-600 text-white shadow-glow-pink-sm'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                   :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                   title="{{ $item['label'] }}">
                    @if ($active)
                        <span class="absolute -left-3 top-1/2 hidden h-6 w-1 -translate-y-1/2 rounded-r-full bg-brand-500 lg:block" x-show="!collapsed"></span>
                    @endif
                    <svg class="h-5 w-5 shrink-0 transition-transform duration-200 group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}"/>
                    </svg>
                    <span class="flex-1 truncate" :class="collapsed ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                    @if (($item['badge'] ?? 0) > 0)
                        <span class="inline-flex min-w-[20px] items-center justify-center rounded-full px-1.5 py-0.5 text-[11px] font-bold
                                     {{ $active ? 'bg-white/25 text-white' : 'bg-brand-500 text-white' }}"
                              :class="collapsed ? 'lg:absolute lg:right-1.5 lg:top-1' : ''">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Collapse toggle (desktop) --}}
        <div class="hidden shrink-0 border-t border-slate-200/80 p-3 dark:border-white/10 lg:block">
            <button @click="toggleCollapse()"
                    class="side-link w-full text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white"
                    :class="collapsed ? 'justify-center px-0' : ''">
                <svg class="h-5 w-5 shrink-0 transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/>
                </svg>
                <span :class="collapsed ? 'lg:hidden' : ''">Collapse</span>
            </button>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="transition-all duration-300" :class="collapsed ? 'lg:pl-[76px]' : 'lg:pl-64'">
        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200/80 bg-white/80 px-4 backdrop-blur-2xl dark:border-white/10 dark:bg-navy-900/80 sm:px-6">
            <button @click="sidebarOpen = true"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white lg:hidden"
                    aria-label="Open menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="min-w-0 flex-1">
                @if ($breadcrumbs)
                    <nav class="mb-0.5 text-xs text-slate-400 dark:text-slate-500">{{ $breadcrumbs }}</nav>
                @endif
                <h1 class="truncate text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h1>
            </div>

            {{-- Global search --}}
            <form action="{{ route('admin.search.index') }}" method="GET" class="hidden md:block">
                <div class="group relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" name="q" placeholder="Search students, teachers…"
                           class="w-64 rounded-xl border-slate-200 bg-slate-100/70 py-2.5 pl-10 pr-4 text-sm transition-all duration-300 placeholder:text-slate-400
                                  focus:w-80 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15
                                  dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:bg-navy-800">
                </div>
            </form>

            @if ($activeSchoolYear ?? null)
                <span class="hidden items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 xl:inline-flex">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    SY {{ $activeSchoolYear->name }}
                </span>
            @else
                <a href="{{ route('admin.school-years.index') }}" class="hidden items-center rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300 xl:inline-flex">No active school year</a>
            @endif

            {{-- Notifications --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white"
                        aria-label="Notifications">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                    </svg>
                    @if ($pending > 0)
                        <span class="absolute right-1.5 top-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-brand-500 px-1 text-[10px] font-bold text-white shadow-glow-pink-sm">{{ $pending }}</span>
                    @endif
                </button>
                <div x-show="open" x-cloak @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-80 origin-top-right overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lift dark:border-white/10 dark:bg-navy-800">
                    <div class="border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-900 dark:border-white/10 dark:text-white">Notifications</div>
                    @if ($pending > 0)
                        <a href="{{ route('admin.registrations.index') }}" class="flex items-start gap-3 px-4 py-3.5 transition-colors hover:bg-slate-50 dark:hover:bg-white/5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/15">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $pending }} pending registration{{ $pending > 1 ? 's' : '' }}</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400">Teacher accounts waiting for approval</span>
                            </span>
                        </a>
                    @else
                        <div class="px-4 py-8 text-center">
                            <p class="text-sm text-slate-500 dark:text-slate-400">You're all caught up 🎉</p>
                        </div>
                    @endif
                </div>
            </div>

            <x-theme-toggle />

            {{-- User dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="group flex cursor-pointer items-center gap-2 rounded-full py-1.5 pl-1.5 pr-1.5 transition-colors hover:bg-slate-100 dark:hover:bg-white/10 sm:pr-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white shadow-glow-pink-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
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
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full cursor-pointer items-center gap-3 border-t border-slate-100 px-4 py-2.5 text-left text-sm text-red-600 transition-colors hover:bg-red-50 dark:border-white/10 dark:hover:bg-red-500/10">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Flash toasts --}}
        @if (session('success') || session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="fixed right-4 top-20 z-50 max-w-sm">
                <div class="flex items-start gap-3 rounded-2xl px-5 py-4 text-white shadow-lift backdrop-blur-sm
                            {{ session('success')
                                ? 'bg-gradient-to-r from-emerald-500 to-emerald-600'
                                : 'bg-gradient-to-r from-red-500 to-red-600' }}">
                    <div class="mt-0.5 flex-shrink-0">
                        @if (session('success'))
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        @endif
                    </div>
                    <span class="flex-1 text-sm font-medium">{{ session('success') ?? session('error') }}</span>
                    <button @click="show = false" class="flex-shrink-0 cursor-pointer text-white/80 transition-colors hover:text-white" aria-label="Dismiss">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            </div>
        @endif

        <main class="animate-fade-in p-4 sm:p-6">
            {{ $slot }}
        </main>
    </div>
</div>

@include('partials.confirm-delete-script')
</body>
</html>
