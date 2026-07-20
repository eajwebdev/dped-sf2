{{-- The hero below is the page heading, so the shell's own <h1> stays off. --}}
<x-app-shell :title="null" wide>
    @php
        // A school head views this same dashboard read-only: the edit actions
        // below are swapped for their read-only oversight equivalents (or hidden)
        // so a supervisor is never offered a teacher-only write screen.
        $isSupervisor = auth()->user()?->isSupervisor();
    @endphp
    <div class="space-y-6">
        {{-- Hero header --}}
        <div class="animate-slide-up relative overflow-hidden rounded-card border border-slate-200/80 bg-white px-6 py-6 shadow-soft
                    dark:border-white/10 dark:bg-gradient-to-br dark:from-navy-800 dark:via-navy-900 dark:to-navy-950 dark:shadow-lift">
            {{-- Decorative brand wash, kept faint in light mode so text stays legible --}}
            <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl dark:bg-brand-500/20"></div>
            <div class="pointer-events-none absolute -bottom-28 left-1/3 h-56 w-56 rounded-full bg-navy-400/10 blur-3xl dark:bg-navy-400/20"></div>

            <div class="relative flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0">
                    <a href="{{ route($isSupervisor ? 'supervisor.insights.index' : 'insights.index') }}"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition-colors hover:text-brand-500 dark:text-slate-400 dark:hover:text-white">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        All classes
                    </a>
                    <h1 class="mt-1.5 truncate text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                        {{ $section->gradeLevel->name }} — {{ $section->name }}
                    </h1>
                    <p class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-200">SY {{ $schoolYear->name }}</span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            </span>
                            Derived live from attendance, book, and promotion records
                        </span>
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    {{-- Attendance sheet is an editing screen — omitted for read-only oversight. --}}
                    @unless ($isSupervisor)
                        <a href="{{ route('attendance.sheet', $section) }}"
                           class="btn-outline btn-sm dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Attendance sheet
                        </a>
                    @endunless
                    <a href="{{ $isSupervisor ? route('supervisor.sf2.show', $section) : route('reports.sf2.index') }}"
                       @if ($isSupervisor) target="_blank" @endif class="btn-primary btn-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                        {{ $isSupervisor ? 'View SF2' : 'Generate SF2' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI tiles --}}
        {{-- Six across from lg so each tile stays a compact single column. --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([
                ['label' => 'Learners', 'value' => $tiles['learners'], 'tone' => 'brand',
                 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                ['label' => 'Days recorded', 'value' => $tiles['daysRecorded'], 'tone' => 'navy',
                 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
                ['label' => 'Avg attendance', 'value' => $tiles['avgRate'].'%', 'tone' => 'success', 'animate' => false,
                 'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941'],
                ['label' => 'Absences', 'value' => $tiles['totalAbsences'], 'tone' => 'danger',
                 'icon' => 'M6 18 18 6M6 6l12 12'],
                ['label' => 'Tardies', 'value' => $tiles['totalTardies'], 'tone' => 'warning',
                 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['label' => 'Perfect attendance', 'value' => $tiles['perfect'], 'tone' => 'info',
                 'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z'],
            ] as $i => $tile)
                <div class="stagger-{{ min($i + 1, 6) }} animate-slide-up">
                    <x-stat-card :label="$tile['label']" :value="$tile['value']" :tone="$tile['tone']"
                                 :icon="$tile['icon']" :animate="$tile['animate'] ?? true" />
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            {{-- Monthly attendance --}}
            <div class="stagger-1 animate-slide-up">
                <x-card hover accent class="h-full">
                    <x-slot:title>Monthly attendance</x-slot:title>
                    <x-slot:actions>
                        <span class="text-[11px] font-medium text-slate-400">Present ÷ possible attendances</span>
                    </x-slot:actions>

                    @if (count($monthlyTrend))
                        @php $bestRate = collect($monthlyTrend)->max('rate'); @endphp
                        {{-- Column chart: each bar's fill scales to its own attendance rate --}}
                        <div class="relative pt-2">
                            {{-- Gridlines at 100 / 75 / 50 / 25 --}}
                            <div class="pointer-events-none absolute inset-x-0 top-2 h-44">
                                @foreach ([0, 25, 50, 75] as $line)
                                    <div class="absolute inset-x-0 border-t border-dashed border-slate-100 dark:border-white/5" style="top: {{ $line }}%"></div>
                                @endforeach
                            </div>

                            <div class="relative flex h-44 items-end gap-2">
                                @foreach ($monthlyTrend as $month)
                                    <div class="group flex h-full flex-1 flex-col justify-end">
                                        <p class="mb-1 text-center text-[11px] font-bold tabular-nums text-slate-500 opacity-0 transition-opacity group-hover:opacity-100 dark:text-slate-300">{{ $month['rate'] }}%</p>
                                        <div class="relative w-full overflow-hidden rounded-t-lg bg-gradient-to-t transition-all duration-500 {{ $month['rate'] === $bestRate ? 'from-brand-600 to-brand-400 shadow-glow-pink-sm' : 'from-navy-600 to-navy-400 dark:from-navy-500 dark:to-navy-300' }}"
                                             style="height: {{ max(2, $month['rate']) }}%"></div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-2 flex gap-2 border-t border-slate-100 pt-2 dark:border-white/5">
                                @foreach ($monthlyTrend as $month)
                                    <span class="flex-1 text-center text-[11px] font-semibold text-slate-400">{{ $month['label'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Nothing to chart yet</p>
                            <p class="mt-1 text-xs text-slate-400">No attendance recorded this school year.</p>
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- Watchlist --}}
            <div class="stagger-2 animate-slide-up">
                <x-card hover :padding="false" class="h-full">
                    <x-slot:title>Attendance watchlist</x-slot:title>
                    <x-slot:actions>
                        @if (count($watchlist))
                            <span class="badge bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">{{ count($watchlist) }} flagged</span>
                        @else
                            <span class="badge bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">All clear</span>
                        @endif
                    </x-slot:actions>

                    <p class="px-6 pt-4 text-[11px] leading-relaxed text-slate-400">
                        Absence streaks of {{ \App\Services\InsightsService::WATCHLIST_STREAK }}+ days, or attendance under
                        {{ \App\Services\InsightsService::LOW_ATTENDANCE_PERCENT }}%. At
                        {{ \App\Services\InsightsService::CRITICAL_STREAK }} straight days DepEd expects intervention.
                    </p>

                    <div class="mt-2 divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($watchlist as $learner)
                            <div class="flex items-center justify-between gap-3 px-6 py-3 transition-colors hover:bg-slate-50/80 dark:hover:bg-white/5">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-[11px] font-bold text-white
                                                 {{ $learner['critical'] ? 'from-red-500 to-red-600' : 'from-amber-500 to-amber-600' }}">
                                        {{ Str::of($learner['name'])->explode(' ')->filter()->take(2)->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)))->implode('') }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $learner['name'] }}</p>
                                        <p class="mt-0.5 text-[11px] tabular-nums text-slate-400">
                                            {{ $learner['rate'] }}% attendance · {{ $learner['absences'] }} {{ Str::plural('absence', $learner['absences']) }}
                                        </p>
                                    </div>
                                </div>

                                @if ($learner['critical'])
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-700 dark:bg-red-500/10 dark:text-red-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.34 3.94L2.7 16.13c-.87 1.5.21 3.37 1.94 3.37h14.72c1.73 0 2.81-1.87 1.94-3.37L13.66 3.94c-.87-1.5-3.03-1.5-3.9 0z"/></svg>
                                        {{ $learner['streak'] }} days straight — act now
                                    </span>
                                @elseif ($learner['reason'] === 'streak')
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $learner['streak'] }} days straight
                                    </span>
                                @else
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                                        low attendance
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-[0_4px_14px_-2px_rgb(34_197_94/0.35)]">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                                <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">Nobody needs attention</p>
                                <p class="mt-1 text-xs text-slate-400">No active absence streaks or low attendance.</p>
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>

            {{-- Most tardies --}}
            <div class="stagger-3 animate-slide-up">
                <x-card hover class="h-full">
                    <x-slot:title>Most tardies</x-slot:title>
                    <x-slot:actions>
                        <span class="text-[11px] font-medium text-slate-400">Top 5 this school year</span>
                    </x-slot:actions>

                    <p class="text-[11px] text-slate-400">The SF2 counts a late learner as attended — the pattern is what matters here.</p>

                    @php $maxTardy = max(1, collect($tardiest)->max('tardies') ?? 1); @endphp
                    <div class="mt-4 space-y-3">
                        @forelse ($tardiest as $i => $learner)
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-[11px] font-bold
                                             {{ $i === 0 ? 'bg-gradient-to-br from-amber-500 to-amber-600 text-white' : 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400' }}">
                                    {{ $i + 1 }}
                                </span>
                                <span class="w-32 shrink-0 truncate text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $learner['name'] }}</span>
                                <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-600 transition-all duration-700"
                                         style="width: {{ (int) round(100 * $learner['tardies'] / $maxTardy) }}%"></div>
                                </div>
                                <span class="w-6 shrink-0 text-right text-xs font-extrabold tabular-nums text-slate-700 dark:text-slate-200">{{ $learner['tardies'] }}</span>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">Excellent punctuality</p>
                                <p class="mt-1 text-xs text-slate-400">No tardies recorded this school year.</p>
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>

            {{-- Books + proficiency, stacked --}}
            <div class="stagger-4 animate-slide-up space-y-4">
                <x-card hover>
                    <x-slot:title>Textbooks (SF3)</x-slot:title>
                    <x-slot:actions>
                        <a href="{{ $isSupervisor ? route('supervisor.sf3.show', $section) : route('books.index', $section) }}"
                           @if ($isSupervisor) target="_blank" @endif class="inline-flex items-center gap-1 text-xs font-semibold text-brand-500 transition-colors hover:text-brand-600 dark:text-brand-400">
                            {{ $isSupervisor ? 'View SF3' : 'Manage' }}
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    </x-slot:actions>

                    @if ($books['issued'] > 0)
                        {{-- Single stacked bar: every issued copy is returned, still out, or lost --}}
                        <div class="flex h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                            @foreach ([
                                ['count' => $books['returned'], 'class' => 'bg-gradient-to-r from-emerald-400 to-emerald-600'],
                                ['count' => $books['outstanding'], 'class' => 'bg-gradient-to-r from-amber-400 to-amber-600'],
                                ['count' => $books['lost'], 'class' => 'bg-gradient-to-r from-red-400 to-red-600'],
                            ] as $seg)
                                @if ($seg['count'] > 0)
                                    <div class="{{ $seg['class'] }}" style="width: {{ 100 * $seg['count'] / $books['issued'] }}%"></div>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ([
                                ['label' => 'Issued', 'value' => $books['issued'], 'wrap' => 'bg-slate-50 dark:bg-white/5', 'text' => 'text-slate-400', 'num' => 'text-slate-900 dark:text-white'],
                                ['label' => 'Returned', 'value' => $books['returned'], 'wrap' => 'bg-emerald-50 dark:bg-emerald-500/10', 'text' => 'text-emerald-600 dark:text-emerald-400', 'num' => 'text-emerald-700 dark:text-emerald-300'],
                                ['label' => 'Still out', 'value' => $books['outstanding'], 'wrap' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-600 dark:text-amber-400', 'num' => 'text-amber-700 dark:text-amber-300'],
                                ['label' => 'Lost', 'value' => $books['lost'], 'wrap' => 'bg-red-50 dark:bg-red-500/10', 'text' => 'text-red-600 dark:text-red-400', 'num' => 'text-red-700 dark:text-red-300'],
                            ] as $stat)
                                <div class="rounded-xl px-3 py-2.5 {{ $stat['wrap'] }}">
                                    <p class="text-[10px] font-bold uppercase tracking-wider {{ $stat['text'] }}">{{ $stat['label'] }}</p>
                                    <p class="mt-0.5 text-xl font-extrabold tabular-nums {{ $stat['num'] }}">{{ $stat['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">No books issued yet</p>
                            <p class="mt-1 text-xs text-slate-400">Record issuances on the SF3 books screen.</p>
                        </div>
                    @endif
                </x-card>

                <x-card hover>
                    <x-slot:title>Proficiency spread (SF5)</x-slot:title>
                    <x-slot:actions>
                        <a href="{{ $isSupervisor ? route('supervisor.sf5.show', $section) : route('reports.sf5.grades', $section) }}"
                           @if ($isSupervisor) target="_blank" @endif class="inline-flex items-center gap-1 text-xs font-semibold text-brand-500 transition-colors hover:text-brand-600 dark:text-brand-400">
                            {{ $isSupervisor ? 'View SF5' : 'Enter averages' }}
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    </x-slot:actions>

                    @php
                        $maxBand = max(1, max($bands));
                        $bandMeta = \App\Services\Sf5ReportService::BANDS;
                        $bandTotal = array_sum($bands);
                        // Beginning → Advanced reads as a red-to-emerald ramp.
                        $bandTone = [
                            'B' => 'from-red-400 to-red-600',
                            'D' => 'from-orange-400 to-orange-600',
                            'AP' => 'from-amber-400 to-amber-600',
                            'P' => 'from-lime-400 to-lime-600',
                            'A' => 'from-emerald-400 to-emerald-600',
                        ];
                    @endphp

                    @if ($bandTotal > 0)
                        <div class="space-y-3">
                            @foreach ($bands as $letter => $count)
                                <div class="flex items-center gap-3" title="{{ $bandMeta[$letter]['label'] }} ({{ $bandMeta[$letter]['note'] }})">
                                    <span class="w-8 shrink-0 text-xs font-extrabold text-slate-500 dark:text-slate-400">{{ $letter }}</span>
                                    <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                                        <div class="h-full rounded-full bg-gradient-to-r {{ $bandTone[$letter] ?? 'from-brand-400 to-brand-600' }} transition-all duration-700"
                                             style="width: {{ (int) round(100 * $count / $maxBand) }}%"></div>
                                    </div>
                                    <span class="w-14 shrink-0 text-right text-xs font-extrabold tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $count }}
                                        <span class="font-medium text-slate-400">· {{ (int) round(100 * $count / $bandTotal) }}%</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-4 border-t border-slate-100 pt-3 text-[10px] text-slate-400 dark:border-white/5">
                            B Beginning · D Developing · AP Approaching · P Proficient · A Advanced
                        </p>
                    @else
                        <div class="py-8 text-center">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">No general averages yet</p>
                            <p class="mt-1 text-xs text-slate-400">Enter them on the SF5 screen to see the spread.</p>
                        </div>
                    @endif
                </x-card>
            </div>
        </div>
    </div>
</x-app-shell>
