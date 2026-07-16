<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('eaj-appicon.png') }}">
        @include('partials.theme-script')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-slate-50 font-sans text-slate-900 antialiased dark:bg-navy-900">
        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-4 py-10">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -left-32 top-1/4 h-80 w-80 animate-blob rounded-full bg-brand-500/10 blur-3xl"></div>
                <div class="absolute -right-24 bottom-1/4 h-72 w-72 animate-blob rounded-full bg-navy-400/10 blur-3xl" style="animation-delay: -6s"></div>
            </div>

            <div class="relative animate-slide-up">
                <a href="/" class="flex flex-col items-center gap-3">
                    <img src="{{ asset('eaj-appicon.png') }}" alt="{{ config('app.name') }}" class="h-16 w-16 rounded-2xl object-contain shadow-glow-pink-sm">
                </a>
            </div>

            <div class="stagger-1 relative mt-6 w-full max-w-md animate-slide-up">
                <div class="card p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
