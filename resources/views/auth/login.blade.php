<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="min-h-full flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            {{-- Logo + Branding --}}
            <div class="text-center mb-8">
                <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" class="h-16 w-auto mx-auto mb-4">
                <h1 class="text-3xl font-bold text-white">{{ config('app.name') }}</h1>
                <p class="text-gray-400 text-sm mt-2">DepEd Student Attendance Management System</p>
            </div>

            {{-- Card --}}
            <div class="bg-white/10 dark:bg-gray-800/40 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-white/20 dark:border-gray-700/50">
                <h2 class="text-2xl font-semibold text-white mb-6 text-center">Welcome back</h2>

                {{-- Session Status --}}
                @if (session('status'))
                    <div class="mb-4 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-200 mb-2">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="w-full px-4 py-2.5 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition {{ $errors->has('email') ? 'border-red-500/50' : '' }}"
                               placeholder="you@example.com">
                        @if ($errors->has('email'))
                            <p class="mt-1.5 text-red-400 text-sm">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-200 mb-2">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="w-full px-4 py-2.5 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition {{ $errors->has('password') ? 'border-red-500/50' : '' }}"
                               placeholder="••••••••">
                        @if ($errors->has('password'))
                            <p class="mt-1.5 text-red-400 text-sm">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="rounded border-gray-600 bg-white/10 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <label for="remember_me" class="ms-2 text-sm text-gray-400 cursor-pointer">
                            Remember me for 30 days
                        </label>
                    </div>

                    {{-- Sign In Button --}}
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white font-semibold py-2.5 rounded-lg transition transform hover:scale-105 active:scale-95 shadow-lg shadow-indigo-500/50 mt-6">
                        Sign in to your account
                    </button>

                    {{-- Forgot Password Link --}}
                    @if (Route::has('password.request'))
                        <div class="text-center pt-2">
                            <a href="{{ route('password.request') }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium transition">
                                Forgot your password?
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            {{-- Footer Info --}}
            <div class="mt-8 text-center">
                <p class="text-gray-500 text-xs">
                    Admin: admin@dpch.edu.ph<br>
                    Teacher: teacher@dpch.edu.ph
                </p>
            </div>
        </div>
    </div>
</body>
</html>
