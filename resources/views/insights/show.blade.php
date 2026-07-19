<x-app-shell :title="null" wide>
    {{-- Header --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('insights.index') }}" class="text-xs text-gray-400 hover:text-gray-600">&larr; All classes</a>
            <h1 class="text-lg font-semibold">Insights — {{ $section->gradeLevel->name }} {{ $section->name }}</h1>
            <p class="text-xs text-gray-400">SY {{ $schoolYear->name }} · derived live from your attendance, book, and promotion records.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('attendance.sheet', $section) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-white/15 dark:text-gray-200 dark:hover:bg-white/5">Attendance sheet</a>
            <a href="{{ route('reports.sf2.index') }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-white/15 dark:text-gray-200 dark:hover:bg-white/5">Generate SF2</a>
        </div>
    </div>

    {{-- KPI tiles --}}
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([
            ['label' => 'Learners', 'value' => $tiles['learners']],
            ['label' => 'Days recorded', 'value' => $tiles['daysRecorded']],
            ['label' => 'Avg attendance', 'value' => $tiles['avgRate'].'%'],
            ['label' => 'Absences recorded', 'value' => $tiles['totalAbsences']],
            ['label' => 'Tardies recorded', 'value' => $tiles['totalTardies']],
            ['label' => 'Perfect attendance', 'value' => $tiles['perfect']],
        ] as $tile)
            <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-navy-800">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ $tile['label'] }}</p>
                <p class="mt-1 text-2xl font-extrabold tabular-nums text-gray-900 dark:text-white">{{ $tile['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Monthly attendance trend --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-navy-800">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">Monthly attendance</h2>
            <p class="text-[11px] text-gray-400">Share of possible attendances marked present, per month.</p>
            <div class="mt-4 space-y-2.5">
                @forelse ($monthlyTrend as $month)
                    <div class="flex items-center gap-3">
                        <span class="w-8 shrink-0 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $month['label'] }}</span>
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ $month['rate'] }}%"></div>
                        </div>
                        <span class="w-10 shrink-0 text-right text-xs font-bold tabular-nums text-gray-700 dark:text-gray-200">{{ $month['rate'] }}%</span>
                    </div>
                @empty
                    <p class="py-4 text-center text-xs text-gray-400">No attendance recorded yet this school year.</p>
                @endforelse
            </div>
        </div>

        {{-- Watchlist --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-navy-800">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">Attendance watchlist</h2>
            <p class="text-[11px] text-gray-400">
                Current absence streaks of {{ \App\Services\InsightsService::WATCHLIST_STREAK }}+ days, or attendance under
                {{ \App\Services\InsightsService::LOW_ATTENDANCE_PERCENT }}%. At
                {{ \App\Services\InsightsService::CRITICAL_STREAK }} straight days DepEd expects intervention.
            </p>
            <div class="mt-3 divide-y divide-gray-100 dark:divide-white/5">
                @forelse ($watchlist as $learner)
                    <div class="flex items-center justify-between gap-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $learner['name'] }}</p>
                            <p class="text-[11px] text-gray-400">
                                {{ $learner['rate'] }}% attendance · {{ $learner['absences'] }} {{ Str::plural('absence', $learner['absences']) }}
                            </p>
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
                    <p class="py-4 text-center text-xs text-gray-400">Nobody needs attention right now — no active streaks or low attendance.</p>
                @endforelse
            </div>
        </div>

        {{-- Frequent tardies --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-navy-800">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">Most tardies</h2>
            <p class="text-[11px] text-gray-400">Late marks this school year — the SF2 counts these as attended, but the pattern matters.</p>
            @php $maxTardy = max(1, collect($tardiest)->max('tardies') ?? 1); @endphp
            <div class="mt-4 space-y-2.5">
                @forelse ($tardiest as $learner)
                    <div class="flex items-center gap-3">
                        <span class="w-36 shrink-0 truncate text-xs font-medium text-gray-700 dark:text-gray-200">{{ $learner['name'] }}</span>
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ (int) round(100 * $learner['tardies'] / $maxTardy) }}%"></div>
                        </div>
                        <span class="w-6 shrink-0 text-right text-xs font-bold tabular-nums text-gray-700 dark:text-gray-200">{{ $learner['tardies'] }}</span>
                    </div>
                @empty
                    <p class="py-4 text-center text-xs text-gray-400">No tardies recorded — excellent punctuality.</p>
                @endforelse
            </div>
        </div>

        {{-- Books + proficiency, stacked --}}
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-navy-800">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Textbooks (SF3)</h2>
                    <a href="{{ route('books.index', $section) }}" class="text-[11px] font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-300">Manage →</a>
                </div>
                @if ($books['issued'] > 0)
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-white/5">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Issued</p>
                            <p class="text-lg font-extrabold tabular-nums text-gray-900 dark:text-white">{{ $books['issued'] }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 px-3 py-2 dark:bg-emerald-500/10">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">Returned</p>
                            <p class="text-lg font-extrabold tabular-nums text-emerald-700 dark:text-emerald-300">{{ $books['returned'] }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 px-3 py-2 dark:bg-amber-500/10">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">Still out</p>
                            <p class="text-lg font-extrabold tabular-nums text-amber-700 dark:text-amber-300">{{ $books['outstanding'] }}</p>
                        </div>
                        <div class="rounded-xl bg-red-50 px-3 py-2 dark:bg-red-500/10">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-red-600 dark:text-red-400">Lost</p>
                            <p class="text-lg font-extrabold tabular-nums text-red-700 dark:text-red-300">{{ $books['lost'] }}</p>
                        </div>
                    </div>
                @else
                    <p class="mt-3 py-2 text-center text-xs text-gray-400">No books issued yet — record them on the SF3 books screen.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-navy-800">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Proficiency spread (SF5)</h2>
                    <a href="{{ route('reports.sf5.grades', $section) }}" class="text-[11px] font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-300">Enter averages →</a>
                </div>
                @php $maxBand = max(1, max($bands)); $bandMeta = \App\Services\Sf5ReportService::BANDS; @endphp
                @if (array_sum($bands) > 0)
                    <div class="mt-4 space-y-2.5">
                        @foreach ($bands as $letter => $count)
                            <div class="flex items-center gap-3">
                                <span class="w-8 shrink-0 text-xs font-bold text-gray-500 dark:text-gray-400" title="{{ $bandMeta[$letter]['label'] }} ({{ $bandMeta[$letter]['note'] }})">{{ $letter }}</span>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div class="h-full rounded-full bg-brand-500" style="width: {{ (int) round(100 * $count / $maxBand) }}%"></div>
                                </div>
                                <span class="w-6 shrink-0 text-right text-xs font-bold tabular-nums text-gray-700 dark:text-gray-200">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-[10px] text-gray-400">B Beginning · D Developing · AP Approaching · P Proficient · A Advanced</p>
                @else
                    <p class="mt-3 py-2 text-center text-xs text-gray-400">No general averages recorded yet — enter them on the SF5 screen.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-shell>
