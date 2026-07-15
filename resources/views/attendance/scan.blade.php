<x-app-shell title="QR Check-in">
    <div x-data="qrScanner({
            checkinUrl: '{{ route('attendance.checkin', $section) }}',
            date: '{{ $date->toDateString() }}',
        })" class="mx-auto max-w-xl">

        <div class="mb-3 flex items-center justify-between">
            <div>
                <a href="{{ route('attendance.sheet', ['section' => $section, 'date' => $date->toDateString()]) }}" class="text-xs text-gray-400 hover:text-gray-600">&larr; Back to sheet</a>
                <h1 class="text-lg font-semibold">{{ $section->gradeLevel->name }} — {{ $section->name }}</h1>
                <p class="text-xs text-gray-400">{{ $date->format('l, M d, Y') }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="relative overflow-hidden rounded-lg bg-black" style="aspect-ratio: 4/3">
                <video x-ref="video" autoplay playsinline muted class="h-full w-full object-cover"></video>
                <div x-show="!cameraOn" class="absolute inset-0 flex items-center justify-center text-sm text-white/70">Camera off</div>
            </div>

            <div class="mt-3 flex gap-2">
                <button @click="toggleCamera()" x-text="cameraOn ? 'Stop camera' : 'Start camera'"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"></button>
                <span x-show="!supported" x-cloak class="self-center text-xs text-amber-500">Camera scanning not supported on this browser — use manual entry.</span>
            </div>

            {{-- Manual fallback --}}
            <form @submit.prevent="submit(manual)" class="mt-4 flex gap-2">
                <input type="text" x-model="manual" placeholder="Or paste/scan token manually"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button class="rounded-lg bg-gray-100 dark:bg-gray-700 px-4 text-sm font-medium">Check in</button>
            </form>

            <p x-show="message" x-cloak class="mt-3 rounded-lg px-3 py-2 text-sm"
               :class="ok ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300'"
               x-text="message"></p>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <h2 class="mb-2 text-sm font-semibold">Checked in (<span x-text="log.length"></span>)</h2>
            <template x-for="entry in log" :key="entry.id">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/50 py-1.5 text-sm last:border-0">
                    <span x-text="entry.name"></span><span class="text-xs text-emerald-600" x-text="entry.time"></span>
                </div>
            </template>
            <p x-show="log.length === 0" class="py-3 text-center text-xs text-gray-400">No check-ins yet.</p>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('qrScanner', (config) => ({
                cameraOn: false,
                supported: 'BarcodeDetector' in window,
                manual: '',
                message: '',
                ok: false,
                log: [],
                stream: null,
                detector: null,
                busy: false,

                async toggleCamera() {
                    if (this.cameraOn) { this.stopCamera(); return; }
                    if (!this.supported) { this.flash(false, 'This browser has no barcode scanner. Use manual entry.'); return; }
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                        this.$refs.video.srcObject = this.stream;
                        this.detector = new BarcodeDetector({ formats: ['qr_code'] });
                        this.cameraOn = true;
                        this.loop();
                    } catch (e) { this.flash(false, 'Could not access camera.'); }
                },
                stopCamera() {
                    this.cameraOn = false;
                    if (this.stream) { this.stream.getTracks().forEach((t) => t.stop()); this.stream = null; }
                },
                async loop() {
                    if (!this.cameraOn) return;
                    try {
                        const codes = await this.detector.detect(this.$refs.video);
                        if (codes.length && !this.busy) { await this.submit(codes[0].rawValue); }
                    } catch (e) { /* frame not ready */ }
                    setTimeout(() => this.loop(), 600);
                },
                async submit(token) {
                    token = (token || '').trim();
                    if (!token || this.busy) return;
                    this.busy = true;
                    try {
                        const res = await window.axios.post(config.checkinUrl, { token, date: config.date });
                        this.flash(true, res.data.name + ' — present');
                        this.log.unshift({ id: Date.now(), name: res.data.name, time: new Date().toLocaleTimeString() });
                        this.manual = '';
                    } catch (err) {
                        this.flash(false, err.response?.data?.message || 'Check-in failed.');
                    } finally {
                        setTimeout(() => { this.busy = false; }, 1200); // debounce repeat scans
                    }
                },
                flash(ok, msg) { this.ok = ok; this.message = msg; },
            }));
        });
    </script>
</x-app-shell>
