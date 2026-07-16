<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    {{-- viewport-fit=cover lets the gradient run under the notch / home indicator.
         Zoom stays enabled: the key input is 24px, so iOS won't zoom on focus anyway. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Class Scanner · {{ config('app.name') }}</title>

    {{-- Installed / web-view chrome --}}
    <meta name="theme-color" content="#0F172A">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Class Scanner">
    <meta name="robots" content="noindex">
    <link rel="apple-touch-icon" href="{{ asset('eaj-appicon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('eaj-appicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Scanning station: one full-bleed screen, no chrome, nothing to scroll past. --}}
<body class="h-full overscroll-none bg-slate-900 text-gray-100 antialiased [-webkit-tap-highlight-color:transparent]">
    <div class="relative flex min-h-[100dvh] flex-col overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">

        {{-- Ambient glow --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -top-24 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-16 h-64 w-64 rounded-full bg-brand-700/10 blur-3xl"></div>
        </div>

        <main class="relative flex flex-1 flex-col justify-center px-5
                     pt-[max(1.5rem,env(safe-area-inset-top))]
                     pb-[max(1.5rem,env(safe-area-inset-bottom))]
                     pl-[max(1.25rem,env(safe-area-inset-left))]
                     pr-[max(1.25rem,env(safe-area-inset-right))]">
            <div class="mx-auto w-full max-w-sm">

                <div class="mb-8 text-center">
                    <img src="{{ asset('eaj-appicon.png') }}" alt="" class="mx-auto mb-4 h-16 w-16 rounded-2xl object-contain">
                    <h1 class="text-2xl font-extrabold tracking-tight text-white">Class Attendance Scanner</h1>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">Enter the key your teacher gave you to start scanning.</p>
                </div>

                @if (session('error'))
                    <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div class="rounded-3xl border border-white/15 bg-white/[0.07] p-6 shadow-2xl shadow-slate-950/50 backdrop-blur-xl sm:p-8">
                    <form method="POST" action="{{ route('class-scan.unlock') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label for="qr_key" class="mb-2 block text-center text-xs font-bold uppercase tracking-[0.2em] text-gray-400">
                                Class Key
                            </label>
                            {{-- text-2xl keeps it >=16px so iOS never zooms the page on focus --}}
                            <input id="qr_key" name="qr_key" type="text" value="{{ old('qr_key') }}"
                                   required autofocus
                                   maxlength="12"
                                   inputmode="text"
                                   autocomplete="off" autocapitalize="characters" autocorrect="off" spellcheck="false"
                                   style="text-transform: uppercase; letter-spacing: 0.3em;"
                                   class="block min-h-[64px] w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-4 text-center font-mono text-2xl font-bold text-white placeholder-gray-600 transition focus:border-brand-500 focus:bg-white/10 focus:outline-none focus:ring-4 focus:ring-brand-500/25 @error('qr_key') border-red-500/60 @enderror"
                                   placeholder="ABC123">
                            @error('qr_key')
                                <p class="mt-2 text-center text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="flex min-h-[56px] w-full touch-manipulation items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-brand-600 to-brand-500 px-6 text-base font-bold text-white shadow-lg shadow-brand-500/30 transition active:scale-[0.98]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                            </svg>
                            Start Scanning
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs leading-relaxed text-gray-500">
                    No sign-in needed — the class key is your access.<br>
                    It works only while the class is running.
                </p>
            </div>
        </main>
    </div>
</body>
</html>
