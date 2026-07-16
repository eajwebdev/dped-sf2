<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scanning · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 text-gray-100 antialiased">
<div x-data="classScan({ checkinUrl: '{{ route('class-scan.checkin') }}' })" class="flex min-h-full flex-col">

    <header class="flex items-center justify-between gap-4 border-b border-white/10 px-4 py-3 sm:px-6">
        <form method="POST" action="{{ route('class-scan.exit') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 hover:bg-white/5 hover:text-white transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Exit
            </button>
        </form>
        <div class="text-center">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-400">Scanning</p>
            <p class="text-sm font-bold text-white">{{ $session->section->gradeLevel->name }} — {{ $session->section->name }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
        </span>
    </header>

    <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-4 p-4 sm:p-6">
        <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black" style="aspect-ratio: 4/3">
            <video x-ref="video" autoplay playsinline muted class="h-full w-full object-cover"></video>

            <div x-show="!cameraOn" class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-gray-950/60">
                <button @click="startCamera()" class="rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 transition-all">
                    Start Scanning
                </button>
                <p x-show="!supported" x-cloak class="max-w-xs text-center text-xs text-amber-400">This browser can't scan with the camera — type the code below instead.</p>
            </div>

            <div x-show="cameraOn" x-cloak class="pointer-events-none absolute inset-0 flex items-center justify-center">
                <div class="h-52 w-52 rounded-2xl border-2 border-white/60 shadow-[0_0_0_9999px_rgba(0,0,0,0.35)]"></div>
            </div>

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
            <form @submit.prevent="submit(manual)" class="flex flex-1 gap-2">
                <input type="text" x-model="manual" placeholder="Or type / scan the card code here"
                       class="w-full rounded-lg border-white/10 bg-white/5 text-sm text-white placeholder-gray-600 px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30">
                <button class="rounded-lg bg-white/10 px-5 text-sm font-bold text-white hover:bg-white/15 transition-colors">Mark Present</button>
            </form>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-3">
                <h2 class="text-sm font-bold text-white">Marked present this session</h2>
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
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('classScan', (config) => ({
                cameraOn: false,
                supported: 'BarcodeDetector' in window,
                manual: '',
                log: [],
                stream: null,
                detector: null,
                busy: false,
                flashVisible: false,
                flashOk: false,
                flashName: '',
                flashDetail: '',

                async startCamera() {
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
                    if (!token || this.busy) return;
                    this.busy = true;
                    try {
                        const res = await window.axios.post(config.checkinUrl, { token });
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
