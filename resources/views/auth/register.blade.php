<x-public-layout title="Create your account">
    <section class="relative overflow-hidden pt-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-32 top-20 h-80 w-80 animate-blob rounded-full bg-brand-500/15 blur-3xl"></div>
            <div class="absolute -right-24 bottom-0 h-72 w-72 animate-blob rounded-full bg-navy-400/20 blur-3xl" style="animation-delay: -6s"></div>
        </div>

        <div class="relative mx-auto flex min-h-[calc(100vh-4rem)] max-w-lg items-center px-4 py-12 sm:px-6">
            <div class="w-full">
                <div class="mb-8 animate-slide-up text-center">
                    <img src="{{ asset('eaj-appicon.png') }}" alt="" class="mx-auto h-14 w-14 rounded-2xl object-contain">
                    <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Create your teacher account</h1>
                    <p class="mt-2 text-sm text-slate-400">Register, then an administrator approves you to start your 2-week free trial.</p>
                </div>

                <div class="stagger-1 animate-slide-up rounded-card border border-white/15 bg-white/[0.06] p-6 shadow-2xl shadow-navy-950/50 backdrop-blur-2xl sm:p-8">
                    <form method="POST" action="{{ route('register') }}" class="space-y-5"
                          x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf

                        @php
                            $darkInput = 'w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 transition-all duration-200 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-500/15';
                        @endphp

                        <div>
                            <label for="name" class="mb-1.5 block text-sm font-medium text-slate-200">Full Name <span class="text-brand-400">*</span></label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                   class="{{ $darkInput }} {{ $errors->has('name') ? '!border-red-400/60' : '' }}" placeholder="Juan Dela Cruz">
                            @error('name')<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-slate-200">Email Address <span class="text-brand-400">*</span></label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                   class="{{ $darkInput }} {{ $errors->has('email') ? '!border-red-400/60' : '' }}" placeholder="you@example.com">
                            @error('email')<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="contact_number" class="mb-1.5 block text-sm font-medium text-slate-200">Contact Number <span class="text-brand-400">*</span></label>
                            <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number') }}" required autocomplete="tel"
                                   class="{{ $darkInput }} {{ $errors->has('contact_number') ? '!border-red-400/60' : '' }}" placeholder="09xx xxx xxxx">
                            @error('contact_number')<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="school_id" class="mb-1.5 block text-sm font-medium text-slate-200">School <span class="text-brand-400">*</span></label>
                            <x-school-select name="school_id" :schools="$schools" :selected="old('school_id')" :required="true" :dark="true" />
                            @error('school_id')<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                            @if ($schools->isEmpty())
                                <p class="mt-1.5 text-xs font-medium text-amber-400">No schools are available yet — please contact your administrator.</p>
                            @endif
                        </div>

                        <div x-data="{ reveal: false }">
                            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-200">Password <span class="text-brand-400">*</span></label>
                            <div class="relative">
                                <input id="password" :type="reveal ? 'text' : 'password'" name="password" required autocomplete="new-password"
                                       class="{{ $darkInput }} pr-11 {{ $errors->has('password') ? '!border-red-400/60' : '' }}" placeholder="••••••••">
                                <button type="button" @click="reveal = !reveal" tabindex="-1"
                                        class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center text-slate-500 transition-colors hover:text-slate-300">
                                    <svg x-show="!reveal" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    <svg x-show="reveal" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                            @error('password')<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-200">Confirm Password <span class="text-brand-400">*</span></label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                   class="{{ $darkInput }}" placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn-primary btn-lg mt-2 w-full" :disabled="submitting">
                            <span x-text="submitting ? 'Creating account…' : 'Create account'">Create account</span>
                        </button>

                        <p class="pt-2 text-center text-sm text-slate-400">
                            Already registered?
                            <a href="{{ route('login') }}" class="font-semibold text-brand-400 transition-colors hover:text-brand-300">Log in</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
