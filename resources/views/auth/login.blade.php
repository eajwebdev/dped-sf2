<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In · {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('eaj-appicon.png') }}">
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-white antialiased dark:bg-navy-900">
    <div class="flex min-h-full">

        {{-- ══ Left: brand panel ══ --}}
        <div class="bg-animated-gradient relative hidden w-1/2 overflow-hidden lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -left-24 top-1/4 h-80 w-80 animate-blob rounded-full bg-brand-500/25 blur-3xl"></div>
                <div class="absolute -right-16 bottom-1/4 h-72 w-72 animate-blob rounded-full bg-navy-400/25 blur-3xl" style="animation-delay: -6s"></div>
                <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:56px_56px]"></div>
            </div>

            <div class="relative p-10">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('eaj-appicon.png') }}" alt="" class="h-10 w-10 rounded-xl object-contain">
                    <span class="text-lg font-extrabold tracking-tight text-white">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="relative px-10 pb-10">
                <blockquote class="max-w-md">
                    <p class="text-2xl font-bold leading-snug text-white xl:text-3xl">
                        Attendance in seconds.<br>
                        <span class="text-gradient-pink">SF2 reports</span> in one click.
                    </p>
                    <p class="mt-4 text-sm leading-relaxed text-slate-300">
                        QR check-in for your whole class, automatic absences, and a print-ready
                        DepEd School Form 2 — built for Filipino teachers.
                    </p>
                </blockquote>

                <div class="mt-8 flex items-center gap-6">
                    @foreach ([['QR check-in', 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z'], ['Auto absences', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'], ['SF2 export', 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z']] as [$label, $icon])
                        <span class="flex items-center gap-2 text-xs font-semibold text-slate-300">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10">
                                <svg class="h-3.5 w-3.5 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </span>
                            {{ $label }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ══ Right: form ══ --}}
        <div class="flex w-full flex-col justify-center px-4 py-12 sm:px-6 lg:w-1/2 lg:px-16 xl:px-24">
            <div class="mx-auto w-full max-w-md">
                {{-- Mobile logo --}}
                <div class="mb-8 text-center lg:hidden">
                    <img src="{{ asset('eaj-appicon.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-16 w-16 rounded-2xl object-contain">
                </div>

                <div class="animate-slide-up">
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Welcome back</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Sign in to manage your classes and attendance.</p>
                </div>

                @if (session('status'))
                    <div class="stagger-1 mt-6 animate-slide-up">
                        <x-alert variant="success">{{ session('status') }}</x-alert>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="stagger-1 mt-8 animate-slide-up space-y-5"
                      x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf

                    <x-form.input label="Email Address" name="email" type="email" :value="old('email')" required
                                  autofocus autocomplete="username" placeholder="you@example.com" />

                    <div>
                        <div class="flex items-center justify-between">
                            <span class="label !mb-0">Password</span>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-500 transition-colors hover:text-brand-600">Forgot password?</a>
                            @endif
                        </div>
                        <div class="mt-1.5" x-data="{ reveal: false }">
                            <div class="relative">
                                <input id="password" :type="reveal ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                       placeholder="••••••••"
                                       class="input pr-11 {{ $errors->has('password') ? 'input-error' : '' }}">
                                <button type="button" @click="reveal = !reveal" tabindex="-1"
                                        class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200">
                                    <svg x-show="!reveal" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    <svg x-show="reveal" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                            @error('password')<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="h-4 w-4 cursor-pointer rounded border-slate-300 text-brand-500 transition-all focus:ring-brand-500/30 dark:border-white/20 dark:bg-navy-900/60">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Remember me for 30 days</span>
                    </label>

                    <button type="submit" class="btn-primary btn-lg w-full" :disabled="submitting">
                        <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="submitting ? 'Signing in…' : 'Sign in to your account'">Sign in to your account</span>
                    </button>

                    @if (Route::has('register'))
                        <p class="pt-2 text-center text-sm text-slate-500 dark:text-slate-400">
                            New here?
                            <a href="{{ route('register') }}" class="font-semibold text-brand-500 transition-colors hover:text-brand-600">Create an account</a>
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </div>
</body>
</html>
