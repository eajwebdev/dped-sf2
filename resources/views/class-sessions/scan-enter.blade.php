<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Class Scanner · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-gray-100">
    <div class="flex min-h-full items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <img src="{{ asset('eaj-appicon.png') }}" alt="{{ config('app.name') }}" class="mx-auto mb-4 h-14 w-auto">
                <h1 class="text-2xl font-bold text-white">Class Attendance Scanner</h1>
                <p class="mt-2 text-sm text-gray-400">Enter the key your teacher gave you to start scanning.</p>
            </div>

            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-2xl border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-xl">
                <form method="POST" action="{{ route('class-scan.unlock') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="qr_key" class="mb-2 block text-sm font-medium text-gray-200">Class Key</label>
                        <input id="qr_key" type="text" name="qr_key" value="{{ old('qr_key') }}" required autofocus autocomplete="off"
                               maxlength="12" style="text-transform: uppercase; letter-spacing: 0.3em;"
                               class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-3 text-center font-mono text-2xl font-bold text-white placeholder-gray-600 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                               placeholder="ABC123">
                        @error('qr_key')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full transform rounded-lg bg-gradient-to-r from-brand-600 to-brand-500 py-3 font-semibold text-white shadow-lg shadow-brand-500/50 transition hover:scale-105 hover:from-brand-700 hover:to-brand-600 active:scale-95">
                        Start Scanning
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
