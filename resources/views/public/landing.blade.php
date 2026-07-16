<x-public-layout title="QR Attendance for Every Classroom">

    {{-- ══════════════ HERO ══════════════ --}}
    <section class="bg-animated-gradient relative flex min-h-screen items-center overflow-hidden pt-16"
             x-data="{ mx: 0, my: 0 }"
             @mousemove.window="mx = ($event.clientX / window.innerWidth - 0.5) * 2; my = ($event.clientY / window.innerHeight - 0.5) * 2">

        {{-- Floating gradient blobs --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-32 top-1/4 h-96 w-96 animate-blob rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="absolute -right-24 top-1/3 h-80 w-80 animate-blob rounded-full bg-navy-500/30 blur-3xl" style="animation-delay: -5s"></div>
            <div class="absolute bottom-0 left-1/3 h-72 w-72 animate-blob rounded-full bg-brand-600/10 blur-3xl" style="animation-delay: -9s"></div>
            {{-- Floating particles --}}
            @foreach ([['12%','18%','2.5','0'], ['85%','25%','2','-2s'], ['70%','70%','3','-4s'], ['20%','75%','2','-1s'], ['45%','15%','1.5','-3s'], ['92%','60%','2.5','-5s']] as [$l, $t, $s, $d])
                <span class="absolute animate-float-slow rounded-full bg-white/20"
                      style="left: {{ $l }}; top: {{ $t }}; width: {{ $s * 4 }}px; height: {{ $s * 4 }}px; animation-delay: {{ $d }}"></span>
            @endforeach
            {{-- Grid overlay --}}
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:56px_56px]"></div>
        </div>

        <div class="relative mx-auto grid w-full max-w-7xl items-center gap-14 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:gap-10">
            {{-- Copy --}}
            <div class="text-center lg:text-left">
                <span class="eyebrow animate-slide-up">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-500"></span>
                    </span>
                    DepEd SF2-ready · Built for Filipino teachers
                </span>

                <h1 class="stagger-1 mt-6 animate-slide-up text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-5xl xl:text-6xl">
                    Attendance in seconds.<br>
                    <span class="text-gradient-pink">SF2 reports</span> in one click.
                </h1>

                <p class="stagger-2 mx-auto mt-6 max-w-xl animate-slide-up text-lg leading-relaxed text-slate-300 lg:mx-0">
                    Start a class, hand off the scanner, and let students tap in. Absences are marked
                    automatically and your DepEd School Form 2 is ready to print — no paperwork, no late nights.
                </p>

                <div class="stagger-3 mt-9 flex animate-slide-up flex-col items-center justify-center gap-3 sm:flex-row lg:justify-start">
                    <a href="{{ route('register') }}" class="btn-primary btn-lg w-full sm:w-auto">
                        Start your free trial
                        <svg class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-lg w-full border border-white/15 text-slate-200 hover:bg-white/5 sm:w-auto">
                        I already have an account
                    </a>
                </div>
                <p class="stagger-4 mt-4 animate-slide-up text-xs text-slate-400">2-week free trial · No credit card required</p>
            </div>

            {{-- Product mockup with mouse parallax --}}
            <div class="stagger-3 relative animate-slide-up"
                 :style="`transform: perspective(1200px) rotateY(${mx * -3}deg) rotateX(${my * 3}deg)`"
                 style="transform-style: preserve-3d">
                {{-- Browser frame --}}
                <div class="card-glass overflow-hidden rounded-card border-white/15 !bg-white/[0.07] shadow-2xl shadow-navy-950/60">
                    <div class="flex items-center gap-2 border-b border-white/10 px-4 py-3">
                        <span class="h-3 w-3 rounded-full bg-red-400/80"></span>
                        <span class="h-3 w-3 rounded-full bg-amber-400/80"></span>
                        <span class="h-3 w-3 rounded-full bg-emerald-400/80"></span>
                        <span class="ml-3 flex-1 truncate rounded-lg bg-white/5 px-3 py-1 text-[11px] text-slate-400">app.eajsystems.com — Attendance</span>
                    </div>
                    <div class="space-y-4 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="h-2.5 w-32 rounded-full bg-white/25"></div>
                                <div class="mt-2 h-2 w-20 rounded-full bg-white/10"></div>
                            </div>
                            <div class="rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-4 py-2 text-xs font-bold text-white shadow-glow-pink-sm">Start Class</div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ([['Present', '42', 'bg-emerald-400', '91%'], ['Absent', '3', 'bg-red-400', '7%'], ['Late', '1', 'bg-amber-400', '2%']] as [$label, $n, $barClass, $w])
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</div>
                                    <div class="mt-1 text-2xl font-extrabold text-white">{{ $n }}</div>
                                    <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full {{ $barClass }}" style="width: {{ $w }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="space-y-2">
                            @foreach ([['JD', 'w-40', true], ['MA', 'w-32', true], ['RC', 'w-36', false]] as [$init, $w, $in])
                                <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/[0.04] px-3.5 py-2.5">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-brand-500/70 to-navy-500/70 text-[10px] font-bold text-white">{{ $init }}</span>
                                    <div class="flex-1"><div class="h-2 {{ $w }} rounded-full bg-white/15"></div></div>
                                    @if ($in)
                                        <span class="flex items-center gap-1 rounded-full bg-emerald-400/15 px-2.5 py-1 text-[10px] font-bold text-emerald-300">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            Present
                                        </span>
                                    @else
                                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold text-slate-400">Scanning…</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Floating QR card --}}
                <div class="card-glass absolute -left-6 -top-8 hidden w-36 animate-float rounded-2xl border-white/15 !bg-white/10 p-3 text-center shadow-xl sm:block" style="transform: translateZ(60px)">
                    <div class="mx-auto grid h-20 w-20 grid-cols-5 gap-0.5 rounded-lg bg-white p-1.5">
                        @foreach ([1,0,1,1,1, 0,1,0,0,1, 1,0,1,0,1, 1,0,0,1,0, 1,1,1,0,1] as $cell)
                            <span class="{{ $cell ? 'bg-navy-900' : 'bg-white' }} rounded-[2px]"></span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-[10px] font-bold text-white">Scan to check in</p>
                </div>

                {{-- Floating success toast --}}
                <div class="card-glass absolute -bottom-6 -right-4 hidden animate-float-slow items-center gap-2.5 rounded-2xl border-white/15 !bg-white/10 px-4 py-3 shadow-xl sm:flex" style="transform: translateZ(40px)">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-400/20">
                        <svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-white">Dela Cruz, Juan</p>
                        <p class="text-[10px] text-emerald-300">Marked present · 7:14 AM</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scroll hint --}}
        <a href="#trusted" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-slate-400 transition-colors hover:text-white" aria-label="Scroll down">
            <svg class="h-6 w-6 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
        </a>
    </section>

    {{-- ══════════════ TRUSTED BY ══════════════ --}}
    <section id="trusted" class="border-y border-white/5 bg-navy-950/40 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <p class="text-center text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Made for every level of Philippine basic education</p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-12 gap-y-4 text-sm font-bold text-slate-400">
                @foreach (['Elementary', 'Junior High School', 'Senior High School', 'Public Schools', 'Private Schools'] as $seg)
                    <span class="flex items-center gap-2 opacity-70 transition-opacity hover:opacity-100">
                        <svg class="h-4 w-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                        {{ $seg }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════ FEATURES ══════════════ --}}
    <section id="features" class="relative py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span class="eyebrow">Features</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Everything a class adviser needs</h2>
                <p class="mt-4 text-lg text-slate-400">From the first scan of the morning to the printed SF2 at month-end.</p>
            </div>

            @php
                $features = [
                    ['QR check-in', 'Start a class to generate a one-time QR key. The assigned scanner unlocks with the key, then students scan themselves present.', 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z'],
                    ['Absent by default', 'Every learner in the section is pre-marked absent when class starts — only the ones who scan flip to present. No one slips through.', 'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z'],
                    ['SF2, done', 'Daily attendance rolls straight into a print-ready DepEd School Form 2, with PDF and Excel export in the official format.', 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
                    ['Your classes, your data', 'Manage your students and subjects for the active school year set by your school — everything scoped to you.', 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'],
                    ['Weekly schedule', 'Lay out your timetable once and launch the right class at the right time, straight from your dashboard.', 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
                    ['Works on any phone', 'Camera scanning runs in the browser — no app to install on the scanning device. Any Android or iPhone works.', 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3'],
                ];
            @endphp

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as $i => [$heading, $body, $icon])
                    <div class="group relative overflow-hidden rounded-card border border-white/10 bg-white/[0.04] p-6 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-brand-500/40 hover:bg-white/[0.07] hover:shadow-glow-pink-sm">
                        {{-- Gradient border sweep on hover --}}
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-500/70 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500/20 to-navy-500/20 text-brand-400 transition-all duration-300 group-hover:scale-110 group-hover:from-brand-500 group-hover:to-brand-600 group-hover:text-white group-hover:shadow-glow-pink-sm">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <h3 class="mt-5 text-base font-bold text-white">{{ $heading }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════ STATS ══════════════ --}}
    <section class="border-y border-white/5 bg-gradient-to-b from-navy-950/60 to-transparent py-16">
        <div class="mx-auto grid max-w-5xl grid-cols-2 gap-8 px-4 text-center sm:px-6 lg:grid-cols-4"
             x-data="{ shown: false }" x-intersect.once="shown = true">
            @foreach ([['3', 's', 'per student scan'], ['100', '%', 'official SF2 format'], ['14', ' days', 'free on approval'], ['0', '', 'apps to install']] as $i => [$n, $suffix, $label])
                <div>
                    <p class="text-4xl font-extrabold tabular-nums text-white sm:text-5xl"
                       x-data="{ v: 0 }"
                       x-effect="if (shown) { const target = {{ $n }}; if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { v = target } else { const t0 = performance.now(); const tick = t => { const p = Math.min((t - t0) / 1100, 1); v = Math.round(target * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(tick) }; requestAnimationFrame(tick) } }">
                        <span class="text-gradient-pink"><span x-text="v">{{ $n }}</span>{{ $suffix }}</span>
                    </p>
                    <p class="mt-2 text-sm font-medium text-slate-400">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══════════════ HOW IT WORKS ══════════════ --}}
    <section id="how-it-works" class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span class="eyebrow">How it works</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Four steps to a finished SF2</h2>
            </div>

            @php
                $steps = [
                    ['Register & get approved', 'Create your teacher account under your school. Your 2-week free trial starts on approval.', 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z'],
                    ['Set up classes & QR cards', 'Add your students and subjects, then print their QR ID cards in one batch.', 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z'],
                    ['Start class, students scan', 'Launch the session from your schedule, unlock the scanner device, and students tap in as they arrive.', 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z'],
                    ['Print your SF2', 'At month-end, your School Form 2 is already filled in. Export to PDF or Excel and submit.', 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z'],
                ];
            @endphp

            <div class="relative mx-auto mt-16 max-w-3xl">
                <div class="absolute bottom-6 left-6 top-6 w-px bg-gradient-to-b from-brand-500 via-brand-500/40 to-transparent"></div>
                <div class="space-y-10">
                    @foreach ($steps as $i => [$heading, $body, $icon])
                        <div class="relative flex gap-6">
                            <span class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-brand-500/30 bg-navy-900 text-brand-400 shadow-glow-pink-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </span>
                            <div class="pt-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-brand-400">Step {{ $i + 1 }}</span>
                                <h3 class="mt-1 text-lg font-bold text-white">{{ $heading }}</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-400">{{ $body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ PRICING ══════════════ --}}
    <section id="pricing" class="border-t border-white/5 bg-navy-950/40 py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span class="eyebrow">Pricing</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">One plan. Everything included.</h2>
                <p class="mt-4 text-lg text-slate-400">Per teacher, starts after your 2-week free trial.</p>
            </div>

            <div class="relative mx-auto mt-14 max-w-md">
                <div class="absolute -inset-px rounded-[20px] bg-gradient-to-b from-brand-500 via-brand-500/40 to-navy-500/40"></div>
                <div class="relative rounded-[19px] bg-navy-900 p-8 text-center">
                    <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-brand-500 to-brand-600 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-glow-pink-sm">Most popular</span>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Teacher Plan</h3>
                    <div class="mt-5 flex items-end justify-center gap-1">
                        <span class="text-6xl font-extrabold tracking-tight text-white">₱299</span>
                        <span class="mb-2 text-sm text-slate-400">/ month</span>
                    </div>
                    <ul class="mt-8 space-y-3.5 text-left text-sm text-slate-300">
                        @foreach (['2-week free trial on approval', 'Unlimited classes & students', 'QR attendance + printable QR ID cards', 'DepEd SF2 export — PDF & Excel', 'Weekly schedule & scan portal', 'Cancel anytime — renew when you need it'] as $perk)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-500/15">
                                    <svg class="h-3 w-3 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                                {{ $perk }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="btn-primary btn-lg mt-9 w-full">Create your account</a>
                    <p class="mt-3 text-xs text-slate-500">No credit card required for the trial.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ FAQ ══════════════ --}}
    <section id="faq" class="py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <div class="text-center">
                <span class="eyebrow">FAQ</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Frequently asked questions</h2>
            </div>

            @php
                $faqs = [
                    ['Do students need to install an app?', 'No. Scanning runs entirely in the browser of the scanning device. Students just present their QR ID card — printed straight from the system.'],
                    ['Is the SF2 report really DepEd-compliant?', 'Yes. Attendance rolls into the official School Form 2 layout, ready to print or export as PDF and Excel.'],
                    ['What happens when my trial ends?', 'Your data stays safe. Subscribe for ₱299/month to keep recording attendance — you can renew only for the months you need.'],
                    ['Can I use it for multiple sections and subjects?', 'Yes — unlimited classes, sections, subjects, and students are included in the single plan.'],
                    ['What devices do I need?', 'Any phone, tablet, or laptop with a camera and a browser. One device acts as the class scanner; you manage everything from your own dashboard.'],
                ];
            @endphp

            <div class="mt-12 space-y-3" x-data="{ open: 0 }">
                @foreach ($faqs as $i => [$q, $a])
                    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04] transition-colors"
                         :class="open === {{ $i }} ? 'border-brand-500/40' : ''">
                        <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                                class="flex w-full cursor-pointer items-center justify-between gap-4 px-6 py-5 text-left"
                                :aria-expanded="open === {{ $i }}">
                            <span class="text-sm font-bold text-white sm:text-base">{{ $q }}</span>
                            <svg class="h-5 w-5 shrink-0 text-brand-400 transition-transform duration-300"
                                 :class="open === {{ $i }} ? 'rotate-45' : ''"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak>
                            <p class="px-6 pb-5 text-sm leading-relaxed text-slate-400">{{ $a }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════ CTA BANNER ══════════════ --}}
    <section class="px-4 pb-24 sm:px-6">
        <div class="bg-animated-gradient relative mx-auto max-w-5xl overflow-hidden rounded-[28px] border border-white/10 px-6 py-16 text-center sm:px-12">
            <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-brand-500/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-navy-400/20 blur-3xl"></div>
            <h2 class="relative text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                Ready to leave the paper logbook behind?
            </h2>
            <p class="relative mx-auto mt-4 max-w-xl text-lg text-slate-300">
                Join teachers who finish their SF2 in one click, not one weekend.
            </p>
            <div class="relative mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="btn-primary btn-lg w-full sm:w-auto">Start your free trial</a>
                <a href="#features" class="btn btn-lg w-full border border-white/15 text-slate-200 hover:bg-white/5 sm:w-auto">See the features</a>
            </div>
        </div>
    </section>
</x-public-layout>
