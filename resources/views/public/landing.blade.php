<x-public-layout title="Automated School Forms System — Automate DepEd School Forms">

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
                    🇵🇭 EAJ ASFS • DepEd-Compliant Automated School Forms System
                </span>

                <h1 class="stagger-1 mt-6 animate-slide-up text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-5xl xl:text-6xl">
                    One Platform.<br>
                    Every DepEd School Form.
                </h1>

                {{-- The strongest marketing line: kept visually loud, directly under the headline. --}}
                <p class="stagger-1 mt-5 animate-slide-up text-2xl font-extrabold leading-snug tracking-tight text-white sm:text-3xl">
                    Attendance in seconds.<br class="hidden sm:block">
                    <span class="text-gradient-pink">School Forms</span> in one click.
                </p>

                <p class="stagger-2 mx-auto mt-6 max-w-xl animate-slide-up text-lg leading-relaxed text-slate-300 lg:mx-0">
                    A modular platform that automates DepEd school reporting. Start a class, hand off the
                    scanner, and let students tap in — absences are recorded automatically and your School
                    Form 2 is ready to print in one click.
                </p>

                {{-- Compact module strip: one live module, the rest by code only (full names live in #features). --}}
                <div class="stagger-3 mt-6 flex animate-slide-up flex-wrap items-center justify-center gap-2 lg:justify-start">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/40 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-200 shadow-glow-emerald-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        SF2 — Daily Attendance
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/40 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-200 shadow-glow-emerald-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        SF1 — School Register
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/40 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-200 shadow-glow-emerald-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        SF3 — Books
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/40 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-200 shadow-glow-emerald-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        SF5 — Promotion
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/40 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-200 shadow-glow-emerald-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        SF8 — Health
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/40 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-200 shadow-glow-emerald-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        SF9 — Report Card
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/40 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-200 shadow-glow-emerald-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        SF10 — Permanent Record
                    </span>
                </div>

                <div class="stagger-3 mt-9 flex animate-slide-up flex-col items-center justify-center gap-3 sm:flex-row lg:justify-start">
                    <a href="{{ route('register') }}" class="btn-primary btn-lg w-full sm:w-auto">
                        Start your free trial
                        <svg class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-lg w-full border border-white/15 text-slate-200 hover:bg-white/5 sm:w-auto">
                        I already have an account
                    </a>
                </div>
                <p class="stagger-4 mx-auto mt-4 max-w-xl animate-slide-up text-xs leading-relaxed text-slate-400 lg:mx-0">
                    Start with SF1, SF2, SF3, SF5, SF8, SF9, and SF10 today and seamlessly expand with additional DepEd School Form modules as they become available.
                </p>
            </div>

            @php
                // The mock dashboard's module list. `available` gates whether the panel
                // shows the real product or an explicit "coming soon" preview — an
                // unreleased module must never look like a working screen.
                // SF2 leads: it is the live module, so it is both the landing state and
                // the start of the scroll sweep (which then runs forward, never backward).
                $mockModules = [
                    ['emoji' => '✅', 'code' => 'SF2',  'label' => 'Attendance',       'title' => 'Daily Attendance',           'blurb' => 'Daily attendance monitoring and automated SF2 generation.',                       'available' => true],
                    ['emoji' => '📋', 'code' => 'SF1',  'label' => 'School Register',  'title' => 'School Register',            'blurb' => 'The class master list — learner profile, address, parents, and enrolment indicators.', 'available' => true],
                    ['emoji' => '📚', 'code' => 'SF3',  'label' => 'Books Issued',     'title' => 'Books Issued & Returned',    'blurb' => 'Every textbook handed out, and what is still outstanding at year-end.',            'available' => true],
                    ['emoji' => '🎓', 'code' => 'SF5',  'label' => 'Promotion',        'title' => 'Promotion & Progress',       'blurb' => 'End-of-year promotion, retention, and level of proficiency per learner.',          'available' => true],
                    ['emoji' => '❤️', 'code' => 'SF8',  'label' => 'Health',           'title' => 'Health & Nutrition',         'blurb' => 'The official Basic Health and Nutrition Report, roster prefilled and ready for the weighing session.', 'available' => true],
                    ['emoji' => '📝', 'code' => 'SF9',  'label' => 'Report Card',      'title' => 'Learner Progress Card',      'blurb' => 'The report card parents receive each grading period — formerly Form 138.',         'available' => true],
                    ['emoji' => '📖', 'code' => 'SF10', 'label' => 'Permanent Rec.',   'title' => 'Permanent Academic Record',  'blurb' => 'The transcript that follows a learner between schools — formerly Form 137.',       'available' => true],
                ];
            @endphp

            {{-- Product mockup: mouse parallax + a module showcase that advances as you scroll --}}
            <div class="stagger-3 relative animate-slide-up"
                 :style="`transform: perspective(1200px) rotateY(${mx * -3}deg) rotateX(${my * 3}deg)`"
                 style="transform-style: preserve-3d"
                 x-data="{
                     i: 0,
                     n: {{ count($mockModules) }},
                     modules: @js($mockModules),
                     paused: false,
                     reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                     get current() { return this.modules[this.i] },
                     init() {
                         this.sync();
                         window.addEventListener('scroll', () => this.sync(), { passive: true });
                         // Idle at the top of the page, the showcase demos itself.
                         if (! this.reduced) {
                             setInterval(() => { if (! this.paused) this.i = (this.i + 1) % this.n }, 2800);
                         }
                     },
                     sync() {
                         // Above the fold the carousel autoplays; once scrolling starts the
                         // scroll position drives which module is on screen.
                         if (window.scrollY < 24) { this.paused = false; return }
                         this.paused = true;
                         const p = Math.min(window.scrollY / (window.innerHeight * 0.85), 1);
                         this.i = Math.min(this.n - 1, Math.round(p * (this.n - 1)));
                     },
                     select(k) { this.paused = true; this.i = k },
                 }">

                {{-- Browser frame --}}
                <div class="card-glass overflow-hidden rounded-card border-white/15 !bg-white/[0.07] shadow-2xl shadow-navy-950/60">
                    <div class="flex items-center gap-2 border-b border-white/10 px-4 py-3">
                        <span class="h-3 w-3 rounded-full bg-red-400/80"></span>
                        <span class="h-3 w-3 rounded-full bg-amber-400/80"></span>
                        <span class="h-3 w-3 rounded-full bg-emerald-400/80"></span>
                        <span class="ml-3 flex-1 truncate rounded-lg bg-white/5 px-3 py-1 text-[11px] text-slate-400">asfs.eajwebdev.com</span>
                    </div>

                    <div class="flex">
                        {{-- Module sidebar: highlight follows the showcase --}}
                        <aside class="hidden w-36 shrink-0 border-r border-white/10 bg-navy-950/40 p-2.5 sm:block">
                            <p class="px-1.5 pb-2 text-[9px] font-bold uppercase tracking-widest text-slate-500">Dashboard</p>
                            <ul class="space-y-px">
                                @foreach ($mockModules as $k => $m)
                                    <li>
                                        <button type="button" @click="select({{ $k }})"
                                                class="flex w-full items-center gap-1.5 rounded-md px-1.5 py-1 text-left transition-all duration-300"
                                                :class="i === {{ $k }}
                                                    ? '{{ $m['available'] ? 'bg-gradient-to-r from-emerald-400/25 to-emerald-500/5 ring-1 ring-inset ring-emerald-400/40' : 'bg-gradient-to-r from-brand-500/25 to-brand-600/10 ring-1 ring-inset ring-brand-500/30' }}'
                                                    : 'opacity-40 hover:opacity-70'">
                                            <span class="text-[9px] leading-none">{{ $m['emoji'] }}</span>
                                            <span class="min-w-0 flex-1 leading-tight">
                                                <span class="block truncate text-[9px] font-bold transition-colors"
                                                      :class="i === {{ $k }} ? 'text-white' : 'text-slate-400'">{{ $m['code'] }}</span>
                                                <span class="block truncate text-[7px] transition-colors"
                                                      :class="i === {{ $k }} ? '{{ $m['available'] ? 'text-emerald-200' : 'text-brand-200' }}' : 'text-slate-500'">{{ $m['label'] }}</span>
                                            </span>
                                            @unless ($m['available'])
                                                <span class="shrink-0 text-[6px] font-bold uppercase tracking-wide text-slate-600">Soon</span>
                                            @endunless
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </aside>

                        {{-- Panel --}}
                        <div class="min-h-[272px] min-w-0 flex-1 p-5">
                            {{-- Breadcrumb tracks the active module --}}
                            <div class="flex items-center gap-1.5 text-[10px] font-semibold">
                                <span class="text-slate-500">Modules</span>
                                <svg class="h-2.5 w-2.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                <span :class="current.available ? 'text-emerald-300' : 'text-brand-300'"
                                      x-text="current.code + ' · ' + current.title">SF2 · Daily Attendance</span>
                            </div>

                            {{-- ── Live module: the real SF2 attendance dashboard ── --}}
                            <div x-show="current.code === 'SF2'" x-transition:enter="transition duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-4 space-y-4">
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

                            {{-- ── Live module: the SF1 School Register ── --}}
                            <div x-show="current.code === 'SF1'" x-cloak
                                 x-transition:enter="transition duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="h-2.5 w-28 rounded-full bg-white/25"></div>
                                        <div class="mt-2 h-2 w-20 rounded-full bg-white/10"></div>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-xs font-bold text-white">Generate SF1</div>
                                </div>
                                {{-- Register rows: LRN, name, sex, age --}}
                                <div class="overflow-hidden rounded-xl border border-white/10">
                                    <div class="flex items-center gap-2 border-b border-white/10 bg-white/[0.06] px-3 py-1.5 text-[8px] font-bold uppercase tracking-wider text-slate-400">
                                        <span class="w-3">#</span><span class="w-14">LRN</span><span class="flex-1">Learner</span><span class="w-4">Sex</span><span class="w-5">Age</span>
                                    </div>
                                    @foreach ([['1', 'w-24', 'M', '12'], ['2', 'w-20', 'M', '11'], ['3', 'w-28', 'F', '12'], ['4', 'w-24', 'F', '11']] as [$n, $w, $sex, $age])
                                        <div class="flex items-center gap-2 border-b border-white/5 px-3 py-1.5 last:border-0">
                                            <span class="w-3 text-[9px] font-bold text-slate-500">{{ $n }}</span>
                                            <span class="h-1.5 w-14 rounded-full bg-white/15"></span>
                                            <span class="flex-1"><span class="block h-1.5 {{ $w }} rounded-full bg-white/20"></span></span>
                                            <span class="w-4 text-[9px] font-bold {{ $sex === 'M' ? 'text-sky-300' : 'text-pink-300' }}">{{ $sex }}</span>
                                            <span class="w-5 text-[9px] font-semibold text-slate-400">{{ $age }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2 text-[9px] font-bold">
                                    <span class="rounded-md bg-white/[0.06] px-2 py-1 text-slate-400">BoSY <span class="text-white">42</span></span>
                                    <span class="rounded-md bg-white/[0.06] px-2 py-1 text-slate-400">EoSY <span class="text-white">41</span></span>
                                    <span class="rounded-md bg-emerald-400/15 px-2 py-1 text-emerald-300">T/I 1</span>
                                    <span class="rounded-md bg-amber-400/15 px-2 py-1 text-amber-300">T/O 2</span>
                                </div>
                            </div>

                            {{-- ── Live module: SF3 book issuance grid ── --}}
                            <div x-show="current.code === 'SF3'" x-cloak
                                 x-transition:enter="transition duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="h-2.5 w-28 rounded-full bg-white/25"></div>
                                        <div class="mt-2 h-2 w-20 rounded-full bg-white/10"></div>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-xs font-bold text-white">Generate SF3</div>
                                </div>
                                {{-- Issuance rows: learner × book status chips --}}
                                <div class="overflow-hidden rounded-xl border border-white/10">
                                    <div class="flex items-center gap-2 border-b border-white/10 bg-white/[0.06] px-3 py-1.5 text-[8px] font-bold uppercase tracking-wider text-slate-400">
                                        <span class="flex-1">Learner</span><span class="w-14 text-center">Math</span><span class="w-14 text-center">Science</span><span class="w-14 text-center">Filipino</span>
                                    </div>
                                    @foreach ([['w-24', 'ret', 'out', 'ret'], ['w-20', 'ret', 'ret', 'ret'], ['w-28', 'out', 'out', 'lost'], ['w-24', 'ret', 'out', 'ret']] as [$w, $b1, $b2, $b3])
                                        <div class="flex items-center gap-2 border-b border-white/5 px-3 py-1.5 last:border-0">
                                            <span class="flex-1"><span class="block h-1.5 {{ $w }} rounded-full bg-white/20"></span></span>
                                            @foreach ([$b1, $b2, $b3] as $s)
                                                <span class="w-14 text-center text-[8px] font-bold {{ $s === 'ret' ? 'text-emerald-300' : ($s === 'lost' ? 'text-red-300' : 'text-amber-300') }}">
                                                    {{ $s === 'ret' ? '✓ ret' : ($s === 'lost' ? 'NEG' : 'out') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2 text-[9px] font-bold">
                                    <span class="rounded-md bg-white/[0.06] px-2 py-1 text-slate-400">Issued <span class="text-white">126</span></span>
                                    <span class="rounded-md bg-emerald-400/15 px-2 py-1 text-emerald-300">Returned 118</span>
                                    <span class="rounded-md bg-amber-400/15 px-2 py-1 text-amber-300">Out 7</span>
                                    <span class="rounded-md bg-red-400/15 px-2 py-1 text-red-300">Lost 1</span>
                                </div>
                            </div>

                            {{-- ── Live module: SF5 promotion & proficiency ── --}}
                            <div x-show="current.code === 'SF5'" x-cloak
                                 x-transition:enter="transition duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="h-2.5 w-28 rounded-full bg-white/25"></div>
                                        <div class="mt-2 h-2 w-20 rounded-full bg-white/10"></div>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-xs font-bold text-white">Generate SF5</div>
                                </div>
                                {{-- Promotion rows: average + action --}}
                                <div class="overflow-hidden rounded-xl border border-white/10">
                                    <div class="flex items-center gap-2 border-b border-white/10 bg-white/[0.06] px-3 py-1.5 text-[8px] font-bold uppercase tracking-wider text-slate-400">
                                        <span class="flex-1">Learner</span><span class="w-16 text-center">Gen. Ave.</span><span class="w-16 text-center">Action</span>
                                    </div>
                                    @foreach ([['w-24', '92.375 (A)', 'PROMOTED', 'text-emerald-300'], ['w-20', '86.50 (P)', 'PROMOTED', 'text-emerald-300'], ['w-28', '78.25 (D)', '*IRREG', 'text-pink-300'], ['w-24', '73.10 (B)', 'RETAINED', 'text-amber-300']] as [$w, $ave, $act, $color])
                                        <div class="flex items-center gap-2 border-b border-white/5 px-3 py-1.5 last:border-0">
                                            <span class="flex-1"><span class="block h-1.5 {{ $w }} rounded-full bg-white/20"></span></span>
                                            <span class="w-16 text-center text-[8px] font-semibold text-slate-300">{{ $ave }}</span>
                                            <span class="w-16 text-center text-[8px] font-bold {{ $color }}">{{ $act }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2 text-[9px] font-bold">
                                    <span class="rounded-md bg-emerald-400/15 px-2 py-1 text-emerald-300">Promoted 40</span>
                                    <span class="rounded-md bg-amber-400/15 px-2 py-1 text-amber-300">Retained 2</span>
                                    <span class="rounded-md bg-white/[0.06] px-2 py-1 text-slate-400">Advanced <span class="text-white">12</span></span>
                                </div>
                            </div>

                            {{-- ── Live module: SF8 health & nutrition roster ── --}}
                            <div x-show="current.code === 'SF8'" x-cloak
                                 x-transition:enter="transition duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="h-2.5 w-28 rounded-full bg-white/25"></div>
                                        <div class="mt-2 h-2 w-20 rounded-full bg-white/10"></div>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-xs font-bold text-white">Generate SF8</div>
                                </div>
                                {{-- Roster rows: learner prefilled, measurement cells left open for the weighing session --}}
                                <div class="overflow-hidden rounded-xl border border-white/10">
                                    <div class="flex items-center gap-2 border-b border-white/10 bg-white/[0.06] px-3 py-1.5 text-[8px] font-bold uppercase tracking-wider text-slate-400">
                                        <span class="flex-1">Learner</span><span class="w-8 text-center">Age</span><span class="w-12 text-center">Wt (kg)</span><span class="w-12 text-center">Ht (m)</span><span class="w-12 text-center">BMI</span>
                                    </div>
                                    @foreach ([['w-24', '12'], ['w-20', '11'], ['w-28', '12'], ['w-24', '11']] as [$w, $age])
                                        <div class="flex items-center gap-2 border-b border-white/5 px-3 py-1.5 last:border-0">
                                            <span class="flex-1"><span class="block h-1.5 {{ $w }} rounded-full bg-white/20"></span></span>
                                            <span class="w-8 text-center text-[9px] font-semibold text-slate-400">{{ $age }}</span>
                                            @foreach (range(1, 3) as $blank)
                                                <span class="w-12"><span class="mx-auto block h-1 w-8 rounded-full border-b border-dashed border-white/25"></span></span>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2 text-[9px] font-bold">
                                    <span class="rounded-md bg-white/[0.06] px-2 py-1 text-slate-400">Male <span class="text-white">26</span></span>
                                    <span class="rounded-md bg-white/[0.06] px-2 py-1 text-slate-400">Female <span class="text-white">22</span></span>
                                    <span class="rounded-md bg-emerald-400/15 px-2 py-1 text-emerald-300">Official DepEd layout</span>
                                </div>
                            </div>

                            {{-- ── Live module: SF9 report card ── --}}
                            <div x-show="current.code === 'SF9'" x-cloak
                                 x-transition:enter="transition duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="h-2.5 w-28 rounded-full bg-white/25"></div>
                                        <div class="mt-2 h-2 w-20 rounded-full bg-white/10"></div>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-xs font-bold text-white">Generate SF9</div>
                                </div>
                                {{-- Report-card rows: learning area × quarter grades + final rating --}}
                                <div class="overflow-hidden rounded-xl border border-white/10">
                                    <div class="flex items-center gap-2 border-b border-white/10 bg-white/[0.06] px-3 py-1.5 text-[8px] font-bold uppercase tracking-wider text-slate-400">
                                        <span class="flex-1">Learning Area</span><span class="w-6 text-center">Q1</span><span class="w-6 text-center">Q2</span><span class="w-6 text-center">Q3</span><span class="w-6 text-center">Q4</span><span class="w-8 text-center">Final</span>
                                    </div>
                                    @foreach ([['w-20', '88', '90', '87', '91', '89'], ['w-24', '90', '86', '88', '92', '89'], ['w-16', '85', '84', '87', '86', '86'], ['w-24', '93', '91', '90', '94', '92']] as [$w, $q1, $q2, $q3, $q4, $f])
                                        <div class="flex items-center gap-2 border-b border-white/5 px-3 py-1.5 last:border-0">
                                            <span class="flex-1"><span class="block h-1.5 {{ $w }} rounded-full bg-white/20"></span></span>
                                            <span class="w-6 text-center text-[8px] text-slate-400">{{ $q1 }}</span>
                                            <span class="w-6 text-center text-[8px] text-slate-400">{{ $q2 }}</span>
                                            <span class="w-6 text-center text-[8px] text-slate-400">{{ $q3 }}</span>
                                            <span class="w-6 text-center text-[8px] text-slate-400">{{ $q4 }}</span>
                                            <span class="w-8 text-center text-[8px] font-bold text-emerald-300">{{ $f }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2 text-[9px] font-bold">
                                    <span class="rounded-md bg-emerald-400/15 px-2 py-1 text-emerald-300">General Ave. 89</span>
                                    <span class="rounded-md bg-white/[0.06] px-2 py-1 text-slate-400">Core Values <span class="text-white">AO</span></span>
                                    <span class="rounded-md bg-white/[0.06] px-2 py-1 text-slate-400">One page per learner</span>
                                </div>
                            </div>

                            {{-- ── Unreleased module: an explicit preview, never a fake working screen ── --}}
                            <div x-show="! current.available" x-cloak
                                 x-transition:enter="transition duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="relative mt-4">
                                {{-- Faint wireframe of the form, deliberately unreadable --}}
                                <div class="space-y-2.5 opacity-25 blur-[1.5px]" aria-hidden="true">
                                    <div class="flex gap-2">
                                        <div class="h-8 flex-1 rounded-lg bg-white/10"></div>
                                        <div class="h-8 w-16 rounded-lg bg-white/10"></div>
                                    </div>
                                    @foreach (['w-full', 'w-11/12', 'w-full', 'w-10/12'] as $w)
                                        <div class="flex items-center gap-2">
                                            <div class="h-6 w-6 shrink-0 rounded bg-white/10"></div>
                                            <div class="h-6 {{ $w }} rounded bg-white/[0.07]"></div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Coming-soon overlay --}}
                                <div class="absolute inset-0 flex flex-col items-center justify-center px-4 text-center">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-navy-900/80 text-lg shadow-lg backdrop-blur-sm"
                                          x-text="current.emoji">📋</span>
                                    <p class="mt-2.5 text-sm font-bold text-white">
                                        <span x-text="current.code">SF1</span> —
                                        <span x-text="current.title">School Register</span>
                                    </p>
                                    <p class="mt-1 max-w-[15rem] text-[11px] leading-relaxed text-slate-400" x-text="current.blurb"></p>
                                    <span class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.07] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-300">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                        Coming soon
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress rail: which module of seven you are looking at --}}
                    <div class="flex items-center gap-1 border-t border-white/10 px-4 py-2.5">
                        @foreach ($mockModules as $k => $m)
                            <button type="button" @click="select({{ $k }})" aria-label="Preview {{ $m['code'] }}"
                                    class="h-1 flex-1 rounded-full transition-all duration-500"
                                    :class="i === {{ $k }} ? '{{ $m['available'] ? 'bg-emerald-400' : 'bg-brand-500' }}' : 'bg-white/10 hover:bg-white/25'"></button>
                        @endforeach
                    </div>
                </div>

                {{-- Attendance-specific props: only shown while the SF2 panel is up --}}
                <div x-show="current.code === 'SF2'" x-cloak
                     x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="opacity-0 scale-90"
                     x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="opacity-0 scale-90"
                     class="card-glass absolute -left-6 bottom-14 hidden w-32 animate-float rounded-2xl border-white/15 !bg-white/10 p-3 text-center shadow-xl lg:block"
                     style="transform: translateZ(60px)">
                    <div class="mx-auto grid h-20 w-20 grid-cols-5 gap-0.5 rounded-lg bg-white p-1.5">
                        @foreach ([1,0,1,1,1, 0,1,0,0,1, 1,0,1,0,1, 1,0,0,1,0, 1,1,1,0,1] as $cell)
                            <span class="{{ $cell ? 'bg-navy-900' : 'bg-white' }} rounded-[2px]"></span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-[10px] font-bold text-white">Scan to check in</p>
                </div>

                <div x-show="current.code === 'SF2'" x-cloak
                     x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="opacity-0 scale-90"
                     x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="opacity-0 scale-90"
                     class="card-glass absolute -bottom-6 -right-4 hidden animate-float-slow items-center gap-2.5 rounded-2xl border-white/15 !bg-white/10 px-4 py-3 shadow-xl lg:flex"
                     style="transform: translateZ(40px)">
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

    {{-- ══════════════ FOR SCHOOL HEADS ══════════════ --}}
    <section id="school-heads" class="relative border-t border-white/5 py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            <div class="relative overflow-hidden rounded-card border border-indigo-400/30 bg-gradient-to-br from-indigo-500/[0.12] via-white/[0.04] to-brand-500/[0.10] p-8 shadow-2xl shadow-navy-950/40 sm:p-10">
                <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-indigo-500/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative flex flex-col items-start gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-400/40 bg-indigo-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-indigo-200">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            For Principals &amp; School Heads
                        </span>
                        <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                            Oversee every teacher's records — in one place
                        </h2>
                        <p class="mt-3 text-base leading-relaxed text-slate-300">
                            Are you a principal or school head? Apply for a <span class="font-semibold text-white">School Head account</span>
                            and get <span class="font-semibold text-white">read-only oversight</span> of every class in your school —
                            view and print any teacher's SF2 and records, without changing a thing. Your teachers stay fully in
                            control of their own classes; you simply get a window into all of them.
                        </p>
                        <p class="mt-2 text-xs text-slate-400">
                            Sign up, choose <span class="font-semibold text-slate-300">Principal / School Head</span>, and your school's
                            administrator approves you — no trial, no billing.
                        </p>
                    </div>
                    <a href="{{ route('register') }}" class="btn-primary btn-lg w-full shrink-0 lg:w-auto">
                        Apply as a School Head
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════ FEATURES ══════════════ --}}
    <section id="features" class="relative py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span class="eyebrow">School Form Modules</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">One platform, every DepEd School Form</h2>
                <p class="mt-4 text-lg text-slate-400">
                    Built for teachers, class advisers, registrars, and school heads. SF1, SF2, SF3, SF5, and SF8
                    are live today — the rest of the adviser's forms are on the way.
                </p>
            </div>

            @php
                // The adviser-owned DepEd School Forms. SF1, SF2, SF3, SF5, SF8, SF9, and SF10 ship today;
                // every other module is explicitly flagged so nothing unfinished reads as available.
                $modules = [
                    ['SF1', 'School Register', 'Your class master list — LRN, learner profile, four-part address, parents, and enrolment indicators, in the official DepEd layout.', true, 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5A3.375 3.375 0 0 0 6.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 0 0-9-9Z'],
                    ['SF2', 'Daily Attendance', 'QR check-in, learners pre-marked absent, autosaving marking grid, and a print-ready DepEd School Form 2 PDF in one click.', true, 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z'],
                    ['SF3', 'Books Issued &amp; Returned', 'Issue textbooks to the whole class in one click, record returns and lost-book codes, and print the official SF3 at year-end.', true, 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
                    ['SF5', 'Promotion &amp; Learning Progress', 'Enter general averages once — the action taken, honor formatting, and level-of-proficiency summary are derived and printed in the official layout.', true, 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5'],
                    ['SF8', 'Health &amp; Nutrition Report', 'The official Basic Health and Nutrition Report — your roster, birthdates, and ages prefilled, with the Weight, Height, BMI, and HFA columns ready for the weighing session.', true, 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
                    ['SF9', 'Learner Progress Report Card', 'The report card parents receive each grading period — formerly Form 138 — generated from your class records.', true, 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
                    ['SF10', 'Learner Permanent Record', 'The learner permanent academic record — formerly Form 137 — with the scholastic record built from your entered quarterly ratings and kept always current.', true, 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z'],
                ];
            @endphp

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($modules as [$code, $name, $body, $available, $icon])
                    <div class="group relative flex flex-col overflow-hidden rounded-card border p-6 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1.5
                                {{ $available
                                    ? 'border-emerald-400/30 bg-emerald-400/[0.06] hover:border-emerald-400/60 hover:shadow-glow-emerald'
                                    : 'border-white/10 bg-white/[0.04] hover:border-brand-500/40 hover:bg-white/[0.07] hover:shadow-glow-pink-sm' }}">
                        {{-- Gradient border sweep --}}
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent {{ $available ? 'via-emerald-400/70' : 'via-brand-500/70 opacity-0 transition-opacity duration-300 group-hover:opacity-100' }} to-transparent"></div>

                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl transition-all duration-300 group-hover:scale-110
                                         {{ $available
                                             ? 'bg-emerald-400/15 text-emerald-300'
                                             : 'bg-gradient-to-br from-brand-500/20 to-navy-500/20 text-brand-400 group-hover:from-brand-500 group-hover:to-brand-600 group-hover:text-white group-hover:shadow-glow-pink-sm' }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </span>
                            @if ($available)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-300">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                    </span>
                                    Available
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/[0.07] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    Coming soon
                                </span>
                            @endif
                        </div>

                        <div class="mt-5 flex items-baseline gap-2">
                            <span class="text-xl font-extrabold tracking-tight {{ $available ? 'text-white' : 'text-slate-300' }}">{{ $code }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider {{ $available ? 'text-emerald-300/70' : 'text-slate-600' }}">Module</span>
                        </div>
                        <h3 class="mt-1 text-base font-bold {{ $available ? 'text-emerald-100' : 'text-slate-200' }}">{!! $name !!}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-400">{{ $body }}</p>

                        @if ($available)
                            <a href="{{ route('register') }}" class="mt-5 inline-flex items-center gap-1.5 text-sm font-bold text-emerald-300 transition-colors hover:text-emerald-200">
                                Start using it today
                                <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @else
                            <p class="mt-5 text-xs font-semibold text-slate-500">In development — not yet included</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <p class="mx-auto mt-10 max-w-2xl text-center text-xs leading-relaxed text-slate-500">
                School Forms 1, 2, 3, 5, 8, 9, and 10 are included today. Any future module is clearly marked
                <span class="font-semibold text-slate-400">Coming soon</span> — it is not part of your subscription until released.
            </p>
        </div>
    </section>

    {{-- ══════════════ PLATFORM CAPABILITIES ══════════════ --}}
    <section id="capabilities" class="relative border-t border-white/5 py-24">
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -right-32 top-10 h-72 w-72 rounded-full bg-brand-500/10 blur-3xl"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span class="eyebrow">Platform</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Built to scale with your school</h2>
                <p class="mt-4 text-lg text-slate-400">
                    One learner record, one school year, one account — shared by every module. Set your school
                    up once and each DepEd form plugs into data you already have, instead of asking you to
                    encode the same learners again.
                </p>
            </div>

            @php
                // Platform-level capabilities: these describe the shared foundation behind
                // every form, not any single module's features.
                $capabilities = [
                    ['One learner record', 'Enrol a learner once and every form draws from the same profile, LRN, and section. No re-encoding the same class for each new form.', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                    ['Totals computed for you', 'Counts, tallies, averages, and percentages are worked out from your records — every form arrives with its summary already filled in.', 'M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm2.498-4.5h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm2.504-2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z'],
                    ['Official DepEd output', 'Records roll straight into the official form layout — a PDF you can print and submit, carrying your school name, School ID, and logo.', 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
                    ['Your school, your data', 'Multi-school by design: each school keeps its own logo, active school year, and records — scoped and isolated from every other school.', 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                    ['Roles that fit a school', 'Administrators manage schools, school years, and sections. Advisers see only their own classes — the same permissions apply to every form.', 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                    ['Nothing to install', 'Everything runs in the browser on any phone, tablet, or laptop — including camera scanning. No app, no setup on the devices you already own.', 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3'],
                ];
            @endphp

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($capabilities as [$heading, $body, $icon])
                    <div class="group relative overflow-hidden rounded-card border border-white/10 bg-white/[0.04] p-6 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-brand-500/40 hover:bg-white/[0.07] hover:shadow-glow-pink-sm">
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
            @foreach ([['3', 's', 'per student scan'], ['100', '%', 'official DepEd format'], ['2', '', 'forms live today'], ['0', '', 'apps to install']] as $i => [$n, $suffix, $label])
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
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Four steps to finished School Forms</h2>
                <p class="mt-4 text-lg text-slate-400">One setup feeds every DepEd form — attendance, grades, health, books, and report cards.</p>
            </div>

            @php
                $steps = [
                    ['Register & get approved', 'Create your teacher account under your school. Your 2-week free trial starts on approval.', 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z'],
                    ['Set up your classes', 'Add your students and subjects, then print their QR ID cards in one batch.', 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z'],
                    ['Record as you go', 'Take daily attendance by QR scan, and enter grades, health, and textbook records through the year.', 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z'],
                    ['Generate any School Form', 'At period-end, SF1, SF2, SF3, SF5, SF8, and SF9 are already filled in from your records. Generate the official PDF and submit.', 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z'],
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
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Pick a plan. Pay ahead, pay less.</h2>
                <p class="mt-4 text-lg text-slate-400">
                    Per teacher, starting after your 2-week free trial. Pay several months up front and save
                    {{ \App\Support\SubscriptionPlans::DISCOUNT_PER_EXTRA_MONTH }}% for every extra month, up to
                    {{ \App\Support\SubscriptionPlans::MAX_DISCOUNT_PERCENT }}%.
                </p>
            </div>

            @php
                // Prices come from the same source the checkout uses, so this page
                // can never advertise an amount the payment page won't honour.
                $plans = \App\Support\SubscriptionPlans::class;
                $tiers = $plans::all();
                $featured = $plans::STARTER;

                /*
                 * Centavos only when they exist: ₱269 stays clean, while ₱0.90
                 * keeps its decimals. Rounding to whole pesos would render a
                 * discounted ₱0.90 and its ₱1.00 list price both as "₱1",
                 * hiding the discount entirely.
                 */
                $money = fn (float $v) => '₱'.number_format($v, round($v, 2) == round($v) ? 0 : 2);
            @endphp

            <div class="mt-14 grid items-start gap-6 lg:grid-cols-3">
                @foreach ($tiers as $key => $tier)
                    @php
                        // List price vs. what the admin's promo actually charges.
                        // Both come from the checkout's own source of truth.
                        $listPrice = $plans::monthlyPrice($key) / 100;
                        $promo = $plans::promoPercent($key);
                        $price = $plans::effectiveMonthlyPrice($key) / 100;
                        $isFeatured = $key === $featured;
                    @endphp

                    @if ($isFeatured)
                        {{-- Featured tier keeps the gradient border treatment --}}
                        <div class="relative lg:order-1">
                            <div class="absolute -inset-px rounded-[20px] bg-gradient-to-b from-brand-500 via-brand-500/40 to-navy-500/40"></div>
                            <div class="relative flex h-full flex-col rounded-[19px] bg-navy-900 p-8">
                                <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-gradient-to-r from-brand-500 to-brand-600 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-glow-pink-sm">Most popular</span>
                                <div class="text-center">
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">{{ $tier['name'] }} Plan</h3>
                                    {{-- The price actually charged is the hero number; the
                                         list price sits beside it, struck through. --}}
                                    <div class="mt-5 flex items-end justify-center gap-1">
                                        <span class="text-5xl font-extrabold tracking-tight text-white sm:text-6xl">{{ $money($price) }}</span>
                                        <span class="mb-2 text-sm text-slate-400">/ month</span>
                                    </div>
                                    @if ($promo > 0)
                                        <p class="mt-2 flex items-center justify-center gap-2 text-sm">
                                            <span class="text-slate-500 line-through">{{ $money($listPrice) }}</span>
                                            <span class="rounded-full bg-emerald-400/15 px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-emerald-300">{{ $promo }}% off</span>
                                        </p>
                                    @endif
                                    <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ $tier['tagline'] }}</p>
                                </div>
                                <ul class="mt-8 flex-1 space-y-3.5 text-left text-sm text-slate-300">
                                    @foreach ($tier['perks'] as $perk)
                                        <li class="flex items-start gap-3">
                                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-500/15">
                                                <svg class="h-3 w-3 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            </span>
                                            {{ $perk['label'] }}
                                        </li>
                                    @endforeach
                                    <li class="flex items-start gap-3">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-500/15">
                                            <svg class="h-3 w-3 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        </span>
                                        2-week free trial on approval
                                    </li>
                                </ul>
                                <a href="{{ route('register') }}" class="btn-primary btn-lg mt-9 w-full">Create your account</a>
                                <p class="mt-3 text-center text-xs text-slate-500">No credit card required for the trial.</p>
                            </div>
                        </div>
                    @else
                        <div class="relative flex h-full flex-col overflow-hidden rounded-card border border-white/10 bg-white/[0.04] p-8 backdrop-blur-sm transition-all duration-300 hover:border-brand-500/40 hover:bg-white/[0.07] {{ $key === $plans::PROFESSIONAL ? 'lg:order-2' : 'lg:order-3' }}">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">{{ $tier['name'] }} Plan</h3>
                            <div class="mt-5 flex flex-wrap items-end gap-x-2 gap-y-1">
                                <span class="text-4xl font-extrabold tracking-tight text-white">{{ $money($price) }}</span>
                                <span class="mb-1.5 text-sm text-slate-400">/ month</span>
                                @if ($promo > 0)
                                    <span class="mb-1.5 text-sm text-slate-500 line-through">{{ $money($listPrice) }}</span>
                                    <span class="mb-1.5 rounded-full bg-emerald-400/15 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-emerald-300">{{ $promo }}% off</span>
                                @endif
                            </div>
                            <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ $tier['tagline'] }}</p>
                            <ul class="mt-8 flex-1 space-y-3.5 text-left text-sm">
                                @foreach ($tier['perks'] as $perk)
                                    @php
                                        // The plan data says which perks are usable today; upcoming
                                        // ones keep the explicit "On release" badge.
                                        $live = $perk['live'];
                                    @endphp
                                    <li class="flex items-start gap-3 {{ $live ? 'text-slate-300' : 'text-slate-500' }}">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $live ? 'bg-emerald-400/15' : 'bg-white/[0.06]' }}">
                                            @if ($live)
                                                <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            @else
                                                <svg class="h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            @endif
                                        </span>
                                        <span>
                                            {{ $perk['label'] }}
                                            @unless ($live)
                                                <span class="ml-1 rounded bg-white/[0.07] px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-500">On release</span>
                                            @endunless
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('register') }}" class="btn btn-lg mt-9 w-full border border-white/15 text-slate-200 hover:bg-white/5">Create your account</a>
                            <p class="mt-3 text-center text-xs text-slate-500">Includes every module that is live today.</p>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Advance-payment savings table --}}
            <div class="mx-auto mt-12 max-w-3xl rounded-card border border-white/10 bg-white/[0.04] p-6 backdrop-blur-sm">
                <p class="text-center text-sm font-bold text-white">Pay ahead and save</p>
                <p class="mt-1 text-center text-xs text-slate-400">
                    Choose 1–{{ $plans::MAX_MONTHS }} months at checkout. The discount applies to the whole purchase.
                </p>
                <div class="mt-5 grid grid-cols-3 gap-3 sm:grid-cols-6">
                    @foreach ([1, 3, 6, 9, 12] as $m)
                        @php $q = $plans::quote($featured, $m); @endphp
                        <div class="rounded-xl border {{ $q['discount'] > 0 ? 'border-emerald-400/25 bg-emerald-400/[0.06]' : 'border-white/10 bg-white/[0.03]' }} px-3 py-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $m }} {{ $m === 1 ? 'month' : 'months' }}</p>
                            <p class="mt-1 text-lg font-extrabold text-white">₱{{ number_format($q['total'] / 100, 0) }}</p>
                            <p class="mt-0.5 text-[10px] font-bold {{ $q['discount'] > 0 ? 'text-emerald-300' : 'text-slate-500' }}">
                                {{ $q['discount'] > 0 ? '−'.$q['discount'].'%' : 'standard' }}
                            </p>
                        </div>
                    @endforeach
                    <div class="col-span-3 flex items-center justify-center rounded-xl border border-dashed border-white/10 px-3 py-3 text-center sm:col-span-1">
                        <p class="text-[10px] leading-relaxed text-slate-400">Starter plan shown</p>
                    </div>
                </div>
            </div>

            <p class="mx-auto mt-8 max-w-2xl text-center text-xs leading-relaxed text-slate-500">
                Every plan includes each School Form module that is already live. Items marked
                <span class="font-semibold text-slate-400">On release</span> unlock automatically when that
                module ships — you are never billed separately for it.
            </p>
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
                    ['Which School Form modules can I use today?', 'Seven are live: SF1 — School Register, SF2 — Daily Attendance, SF3 — Books Issued and Returned, SF5 — Promotion and Level of Proficiency, SF8 — Learner\'s Basic Health and Nutrition Report, SF9 — Learner Progress Report Card, and SF10 — Learner Permanent Academic Record, all generated as print-ready PDFs in the official DepEd layout. Any future module is clearly marked Coming soon across this page, and you are never billed for a module before it is released.'],
                    ['Do students need to install an app?', 'No. Scanning runs entirely in the browser of the scanning device. Students just present their QR ID card — printed straight from the system.'],
                    ['Is the SF2 report really DepEd-compliant?', 'Yes. Attendance rolls into the official School Form 2 layout, generated as a PDF you can view and print, carrying your school\'s name, School ID, and logo.'],
                    ['What happens when my trial ends?', 'Your data stays safe. Subscribe from ₱'.number_format(\App\Support\SubscriptionPlans::monthlyPrice(\App\Support\SubscriptionPlans::STARTER) / 100, 0).'/month to keep recording attendance. You choose how many months to buy — 1 to '.\App\Support\SubscriptionPlans::MAX_MONTHS.' — and each extra month paid in advance takes another '.\App\Support\SubscriptionPlans::DISCOUNT_PER_EXTRA_MONTH.'% off, up to '.\App\Support\SubscriptionPlans::MAX_DISCOUNT_PERCENT.'%.'],
                    ['Can I use it for multiple sections and subjects?', 'Yes — unlimited classes, sections, subjects, and students are included in the Starter Plan.'],
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
                Join teachers who finish their SF2 in one click, not one weekend — on a platform
                built to take on every DepEd School Form next.
            </p>
            <div class="relative mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="btn-primary btn-lg w-full sm:w-auto">Start your free trial</a>
                <a href="#features" class="btn btn-lg w-full border border-white/15 text-slate-200 hover:bg-white/5 sm:w-auto">See the modules</a>
            </div>
        </div>
    </section>
</x-public-layout>
