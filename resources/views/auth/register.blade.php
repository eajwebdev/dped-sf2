<x-public-layout title="Create your account">
    <section class="relative overflow-hidden pt-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-32 top-20 h-80 w-80 animate-blob rounded-full bg-brand-500/15 blur-3xl"></div>
            <div class="absolute -right-24 bottom-0 h-72 w-72 animate-blob rounded-full bg-navy-400/20 blur-3xl" style="animation-delay: -6s"></div>
        </div>

        <div class="relative mx-auto flex min-h-[calc(100vh-4rem)] max-w-2xl items-center px-4 py-10 sm:px-6"
             x-data="{ submitting: false, role: '{{ old('role', 'teacher') }}' }">
            <div class="w-full">
                <div class="mb-6 animate-slide-up text-center">
                    <img src="{{ asset('eaj-appicon.png') }}" alt="" class="mx-auto h-12 w-12 rounded-2xl object-contain">
                    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        <span x-text="role === 'supervisor' ? 'Create your school head account' : 'Create your teacher account'">Create your teacher account</span>
                    </h1>
                    <p class="mt-1.5 text-sm text-slate-400"
                       x-text="role === 'supervisor'
                            ? 'Register, then an administrator approves you to oversee your teachers\' records.'
                            : 'Register, then an administrator approves you to start your 2-week free trial.'">
                        Register, then an administrator approves you to start your 2-week free trial.
                    </p>
                </div>

                @php
                    $darkInput = 'w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 transition-all duration-200 focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-500/15';
                @endphp

                <div class="stagger-1 animate-slide-up rounded-card border border-white/15 bg-white/[0.06] p-5 shadow-2xl shadow-navy-950/50 backdrop-blur-2xl sm:p-7">
                    <form method="POST" action="{{ route('register') }}" class="space-y-5" enctype="multipart/form-data" @submit="submitting = true">
                        @csrf

                        {{-- 1 · Role. Teachers get a trial and own their classes; school heads get read-only oversight. --}}
                        <fieldset>
                            <legend class="mb-1.5 text-sm font-semibold text-slate-200">I am registering as <span class="text-brand-400">*</span></legend>
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <label :class="role === 'teacher' ? 'border-brand-400 bg-brand-500/10' : 'border-white/10 bg-white/5 hover:border-white/25'"
                                       class="flex cursor-pointer items-start gap-2.5 rounded-xl border p-3 transition-colors">
                                    <input type="radio" name="role" value="teacher" x-model="role" class="mt-0.5 accent-brand-500">
                                    <span>
                                        <span class="block text-sm font-semibold text-white">Teacher / Adviser</span>
                                        <span class="mt-0.5 block text-xs text-slate-400">Manage my classes, attendance & School Forms.</span>
                                    </span>
                                </label>
                                <label :class="role === 'supervisor' ? 'border-brand-400 bg-brand-500/10' : 'border-white/10 bg-white/5 hover:border-white/25'"
                                       class="flex cursor-pointer items-start gap-2.5 rounded-xl border p-3 transition-colors">
                                    <input type="radio" name="role" value="supervisor" x-model="role" class="mt-0.5 accent-brand-500">
                                    <span>
                                        <span class="block text-sm font-semibold text-white">Principal / School Head</span>
                                        <span class="mt-0.5 block text-xs text-slate-400">View & print every teacher's records (read-only).</span>
                                    </span>
                                </label>
                            </div>
                            @error('role')<p class="mt-1.5 text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                        </fieldset>

                        {{-- 2 · Your details --}}
                        <div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="name" class="mb-1 block text-xs font-medium text-slate-300">Full Name <span class="text-brand-400">*</span></label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                       class="{{ $darkInput }} {{ $errors->has('name') ? '!border-red-400/60' : '' }}" placeholder="Juan Dela Cruz">
                                @error('name')<p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="email" class="mb-1 block text-xs font-medium text-slate-300">Email Address <span class="text-brand-400">*</span></label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                       class="{{ $darkInput }} {{ $errors->has('email') ? '!border-red-400/60' : '' }}" placeholder="you@example.com">
                                @error('email')<p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="contact_number" class="mb-1 block text-xs font-medium text-slate-300">Contact Number <span class="text-brand-400">*</span></label>
                                <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number') }}" required autocomplete="tel"
                                       class="{{ $darkInput }} {{ $errors->has('contact_number') ? '!border-red-400/60' : '' }}" placeholder="09xx xxx xxxx">
                                @error('contact_number')<p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="school_id" class="mb-1 block text-xs font-medium text-slate-300">School <span class="text-brand-400">*</span></label>
                                <x-school-select name="school_id" :schools="$schools" :selected="old('school_id')" :required="true" :dark="true" />
                                @error('school_id')<p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                                @if ($schools->isEmpty())
                                    <p class="mt-1 text-xs font-medium text-amber-400">No schools available yet — please contact your administrator.</p>
                                @endif
                            </div>
                        </div>

                        {{-- 3 · Identity check. Proves the applicant works at the chosen school. --}}
                        <div class="rounded-xl border border-brand-400/25 bg-brand-500/[0.07] p-4">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.746 3.746 0 0 1 21 12Z"/>
                                </svg>
                                <p class="text-sm font-semibold text-white">School ID verification</p>
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-slate-400">Only your school's administrator sees this — it is never shown publicly.</p>

                            <div class="mt-3 grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                                <div>
                                    <label for="school_id_number" class="mb-1 block text-xs font-medium text-slate-300">ID / Employee Number <span class="text-brand-400">*</span></label>
                                    <input id="school_id_number" type="text" name="school_id_number" value="{{ old('school_id_number') }}" required maxlength="60"
                                           class="{{ $darkInput }} {{ $errors->has('school_id_number') ? '!border-red-400/60' : '' }}" placeholder="e.g. 2019-04821">
                                    @error('school_id_number')<p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                                </div>

                                <div x-data="idUpload()">
                                    <label class="mb-1 block text-xs font-medium text-slate-300">Photo of your School ID <span class="text-brand-400">*</span></label>
                                    <label for="school_id_document"
                                           @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                                           @drop.prevent="pick($event.dataTransfer.files[0])"
                                           class="flex h-[42px] cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed px-3 text-center transition-colors"
                                           :class="dragging ? 'border-brand-400 bg-brand-500/10' : 'border-white/15 bg-white/[0.03] hover:border-white/25'">
                                        <template x-if="!preview">
                                            <span class="flex items-center gap-2 text-xs text-slate-400">
                                                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/>
                                                </svg>
                                                Tap to photo or upload
                                            </span>
                                        </template>
                                        <template x-if="preview">
                                            <span class="flex items-center gap-2">
                                                <img :src="preview" alt="School ID preview" class="h-8 w-8 rounded object-cover">
                                                <span class="truncate text-xs font-semibold text-brand-400">Change photo</span>
                                            </span>
                                        </template>
                                    </label>
                                    {{-- capture lets a phone open the camera directly --}}
                                    <input id="school_id_document" type="file" name="school_id_document" required class="sr-only"
                                           accept="image/jpeg,image/png,image/webp" capture="environment"
                                           @change="pick($event.target.files[0])">
                                    <p class="mt-1 text-[11px] text-slate-500">JPG, PNG or WEBP · up to 8&nbsp;MB</p>
                                    <p x-show="tooBig" x-cloak class="mt-1 text-xs font-medium text-red-400">That image is over 8&nbsp;MB.</p>
                                    @error('school_id_document')<p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- 4 · Password --}}
                        <div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2" x-data="{ reveal: false }">
                            <div>
                                <label for="password" class="mb-1 block text-xs font-medium text-slate-300">Password <span class="text-brand-400">*</span></label>
                                <div class="relative">
                                    <input id="password" :type="reveal ? 'text' : 'password'" name="password" required autocomplete="new-password"
                                           class="{{ $darkInput }} pr-11 {{ $errors->has('password') ? '!border-red-400/60' : '' }}" placeholder="••••••••">
                                    <button type="button" @click="reveal = !reveal" tabindex="-1"
                                            class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center text-slate-500 transition-colors hover:text-slate-300">
                                        <svg x-show="!reveal" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        <svg x-show="reveal" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                    </button>
                                </div>
                                @error('password')<p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="mb-1 block text-xs font-medium text-slate-300">Confirm Password <span class="text-brand-400">*</span></label>
                                <input id="password_confirmation" :type="reveal ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                                       class="{{ $darkInput }}" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary btn-lg w-full" :disabled="submitting">
                            <span x-text="submitting ? 'Creating account…' : 'Create account'">Create account</span>
                        </button>

                        <p class="text-center text-sm text-slate-400">
                            Already registered?
                            <a href="{{ route('login') }}" class="font-semibold text-brand-400 transition-colors hover:text-brand-300">Log in</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        // Client-side preview only — the server re-validates type, size and
        // dimensions, so nothing here is trusted.
        function idUpload() {
            return {
                preview: null,
                filename: '',
                dragging: false,
                tooBig: false,
                pick(file) {
                    this.dragging = false;
                    if (!file) return;

                    this.tooBig = file.size > 8 * 1024 * 1024;
                    if (this.tooBig) { this.preview = null; return; }

                    this.filename = file.name;
                    const input = document.getElementById('school_id_document');
                    if (input.files[0] !== file) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                    }
                    const reader = new FileReader();
                    reader.onload = e => this.preview = e.target.result;
                    reader.readAsDataURL(file);
                },
            };
        }
    </script>
    @endpush
</x-public-layout>
