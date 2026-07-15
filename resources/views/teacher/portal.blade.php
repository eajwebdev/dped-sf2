<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scan Portal · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- The portal is a scanning station handed to students: one dark, focused screen. --}}
<body class="h-full bg-gray-950 text-gray-100 antialiased">
<div x-data="scanPortal({
        sections: @js($sections->map(fn ($s) => ['id' => $s->id, 'label' => $s->gradeLevel->name.' — '.$s->name])->values()),
        currentSectionId: @js($currentClass?->section_id),
        checkinUrlTemplate: '{{ url('/attendance') }}/__SECTION__/checkin',
        date: '{{ $now->toDateString() }}',
     })" class="flex min-h-full flex-col">

    {{-- Top bar --}}
    <header class="flex items-center justify-between gap-4 border-b border-white/10 px-4 py-3 sm:px-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 hover:bg-white/5 hover:text-white transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Exit Portal
            </a>
        </div>
        <div class="text-center">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-400">Attendance Scan Portal</p>
            <p class="text-xs text-gray-500" x-text="clock"></p>
        </div>
        <div class="w-[104px] text-right">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
            </span>
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-4 p-4 sm:p-6">

        {{-- Schedule-aware class banner --}}
        @if ($currentClass)
            <div class="rounded-2xl border border-indigo-500/30 bg-indigo-500/10 px-5 py-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-400">Happening now · from your schedule</p>
                <div class="mt-1 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h1 class="text-xl font-bold text-white">{{ $currentClass->section->gradeLevel->name }} — {{ $currentClass->section->name }}</h1>
                    <span class="text-sm text-indigo-300 tabular-nums">{{ $currentClass->time_range }}</span>
                    @if ($currentClass->subject)<span class="rounded-full bg-indigo-500/20 px-2.5 py-0.5 text-xs font-medium text-indigo-300">{{ $currentClass->subject->name }}</span>@endif
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                <p class="text-sm text-gray-400">
                    No class on your schedule right now — pick the section to take attendance for.
                    <a href="{{ route('schedule.index') }}" class="font-medium text-indigo-400 hover:text-indigo-300">Set up your schedule</a>
                    so the portal selects it automatically.
                </p>
            </div>
        @endif

        {{-- Section selector --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Class</label>
            <select x-model="sectionId" class="flex-1 rounded-lg border-white/10 bg-white/5 text-sm text-white px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                <option value="" class="bg-gray-900">Choose a class…</option>
                <template x-for="s in sections" :key="s.id">
                    <option :value="s.id" x-text="s.label" class="bg-gray-900"></option>
                </template>
            </select>
        </div>

        {{-- Scanner stage --}}
        <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black" style="aspect-ratio: 4/3">
            <video x-ref="video" autoplay playsinline muted class="h-full w-full object-cover"></video>

            {{-- Idle state --}}
            <div x-show="!cameraOn" class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-gray-950/60">
                <svg class="h-16 w-16 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/></svg>
                <button @click="startCamera()" :disabled="!sectionId"
                        class="rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all disabled:opacity-40 disabled:shadow-none">
                    Start Scanning
                </button>
                <p x-show="!sectionId" class="text-xs text-gray-500">Choose a class first</p>
                <p x-show="!supported" x-cloak class="max-w-xs text-center text-xs text-amber-400">This browser can't scan with the camera — type the code below instead.</p>
            </div>

            {{-- Scan frame overlay --}}
            <div x-show="cameraOn" x-cloak class="pointer-events-none absolute inset-0 flex items-center justify-center">
                <div class="h-52 w-52 rounded-2xl border-2 border-white/60 shadow-[0_0_0_9999px_rgba(0,0,0,0.35)]"></div>
            </div>

            {{-- Result flash overlay --}}
            <div x-show="flashVisible" x-cloak x-transition.opacity.duration.200ms
                 class="absolute inset-0 flex flex-col items-center justify-center gap-2"
                 :class="flashOk ? 'bg-emerald-600/90' : 'bg-red-600/90'">
                <svg x-show="flashOk" class="h-20 w-20 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <svg x-show="!flashOk" class="h-20 w-20 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="px-6 text-center text-2xl font-bold text-white" x-text="flashName"></p>
                <p class="text-sm font-medium text-white/85" x-text="flashDetail"></p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <button x-show="cameraOn" x-cloak @click="stopCamera()" class="rounded-lg border border-white/15 px-4 py-2 text-sm font-medium text-gray-300 hover:bg-white/5 transition-colors">Stop camera</button>
            {{-- Manual fallback --}}
            <form @submit.prevent="submit(manual)" class="flex flex-1 gap-2">
                <input type="text" x-model="manual" placeholder="Or type / scan the card code here" :disabled="!sectionId"
                       class="w-full rounded-lg border-white/10 bg-white/5 text-sm text-white placeholder-gray-600 px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 disabled:opacity-40">
                <button :disabled="!sectionId" class="rounded-lg bg-white/10 px-5 text-sm font-bold text-white hover:bg-white/15 transition-colors disabled:opacity-40">Mark Present</button>
            </form>
        </div>

        {{-- Session log --}}
        <div class="rounded-2xl border border-white/10 bg-white/5">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-3">
                <h2 class="text-sm font-bold text-white">Checked in this session</h2>
                <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-bold text-emerald-400 tabular-nums" x-text="log.length"></span>
            </div>
            <div class="max-h-64 overflow-y-auto">
                <template x-for="entry in log" :key="entry.id">
                    <div class="flex items-center justify-between border-b border-white/5 px-5 py-2.5 text-sm last:border-0">
                        <span class="font-medium text-gray-200" x-text="entry.name"></span>
                        <span class="text-xs text-emerald-400 tabular-nums" x-text="entry.time"></span>
                    </div>
                </template>
                <p x-show="log.length === 0" class="px-5 py-8 text-center text-xs text-gray-600">Waiting for the first scan…</p>
            </div>
        </div>

        {{-- Today's timetable strip --}}
        @if ($todayClasses->isNotEmpty())
            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">Your classes today</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($todayClasses as $tc)
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium tabular-nums
                                     {{ $currentClass && $tc->id === $currentClass->id ? 'bg-indigo-500/20 text-indigo-300 ring-1 ring-indigo-500/40' : 'bg-white/5 text-gray-400' }}">
                            {{ $tc->section->gradeLevel->name }} {{ $tc->section->name }} · {{ $tc->time_range }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('scanPortal', (config) => ({
                sections: config.sections,
                sectionId: config.currentSectionId ? String(config.currentSectionId) : (config.sections.length === 1 ? String(config.sections[0].id) : ''),
                cameraOn: false,
                supported: 'BarcodeDetector' in window,
                manual: '',
                log: [],
                stream: null,
                detector: null,
                busy: false,
                clock: '',
                flashVisible: false,
                flashOk: false,
                flashName: '',
                flashDetail: '',

                init() {
                    const tick = () => { this.clock = new Date().toLocaleString(undefined, { weekday: 'long', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }); };
                    tick(); setInterval(tick, 15000);
                },
                checkinUrl() {
                    return config.checkinUrlTemplate.replace('__SECTION__', this.sectionId);
                },
                async startCamera() {
                    if (!this.sectionId) return;
                    if (!this.supported) { this.flash(false, 'Not supported', 'Use the code box below'); return; }
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                        this.$refs.video.srcObject = this.stream;
                        this.detector = new BarcodeDetector({ formats: ['qr_code'] });
                        this.cameraOn = true;
                        this.loop();
                    } catch (e) { this.flash(false, 'Camera blocked', 'Allow camera access and retry'); }
                },
                stopCamera() {
                    this.cameraOn = false;
                    if (this.stream) { this.stream.getTracks().forEach((t) => t.stop()); this.stream = null; }
                },
                async loop() {
                    if (!this.cameraOn) return;
                    try {
                        const codes = await this.detector.detect(this.$refs.video);
                        if (codes.length && !this.busy) await this.submit(codes[0].rawValue);
                    } catch (e) { /* frame not ready */ }
                    setTimeout(() => this.loop(), 500);
                },
                async submit(token) {
                    token = (token || '').trim();
                    if (!token || this.busy || !this.sectionId) return;
                    this.busy = true;
                    try {
                        const res = await window.axios.post(this.checkinUrl(), { token, date: config.date, status: 'present' });
                        this.flash(true, res.data.name, 'Marked present');
                        this.log.unshift({ id: Date.now(), name: res.data.name, time: new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) });
                        this.manual = '';
                    } catch (err) {
                        this.flash(false, 'Not recorded', err.response?.data?.message || 'Check-in failed.');
                    } finally {
                        setTimeout(() => { this.busy = false; }, 1500);
                    }
                },
                flash(ok, name, detail) {
                    this.flashOk = ok; this.flashName = name; this.flashDetail = detail; this.flashVisible = true;
                    setTimeout(() => { this.flashVisible = false; }, 1400);
                },
            }));
        });
    </script>
</div>
</body>
</html>
