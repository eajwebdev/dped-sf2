@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('eaj-appicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-navy-900 text-slate-100 antialiased" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 12">
    {{-- Top bar --}}
    <header class="fixed inset-x-0 top-0 z-40 transition-all duration-300"
            :class="scrolled ? 'border-b border-white/10 bg-navy-900/80 backdrop-blur-2xl shadow-lg shadow-navy-950/40' : 'bg-transparent'">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('eaj-appicon.png') }}" alt="" class="h-9 w-9 rounded-xl object-contain">
                <span class="hidden text-base font-extrabold tracking-tight text-white sm:block">{{ config('app.name') }}</span>
            </a>
            <nav class="hidden items-center gap-7 text-sm font-medium text-slate-300 md:flex">
                <a href="#features" class="transition-colors hover:text-white">Features</a>
                <a href="#how-it-works" class="transition-colors hover:text-white">How it works</a>
                <a href="#pricing" class="transition-colors hover:text-white">Pricing</a>
                <a href="#faq" class="transition-colors hover:text-white">FAQ</a>
            </nav>
            <nav class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('login') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-white/5 hover:text-white">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary btn-sm sm:btn-md">Get started</a>
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-white/10 bg-navy-950/60">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
            <div class="flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
                <div>
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('eaj-appicon.png') }}" alt="" class="h-9 w-9 rounded-xl object-contain">
                        <span class="text-base font-extrabold tracking-tight text-white">{{ config('app.name') }}</span>
                    </div>
                    <p class="mt-3 max-w-sm text-sm leading-relaxed text-slate-400">
                        QR attendance and DepEd SF2 reporting for Filipino teachers — scan in seconds, print-ready reports.
                    </p>
                </div>
                <div class="flex flex-wrap gap-x-10 gap-y-4 text-sm">
                    <div class="flex flex-col gap-2.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Product</span>
                        <a href="#features" class="text-slate-300 transition-colors hover:text-white">Features</a>
                        <a href="#pricing" class="text-slate-300 transition-colors hover:text-white">Pricing</a>
                    </div>
                    <div class="flex flex-col gap-2.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Account</span>
                        <a href="{{ route('login') }}" class="text-slate-300 transition-colors hover:text-white">Log in</a>
                        <a href="{{ route('register') }}" class="text-slate-300 transition-colors hover:text-white">Create account</a>
                    </div>
                </div>
            </div>
            <div class="mt-10 border-t border-white/10 pt-6 text-center text-xs text-slate-500">
                © {{ date('Y') }} {{ config('app.name') }} · An EAJ Systems product · DepEd SF2 Attendance Management
            </div>
        </div>
    </footer>
</body>
</html>
