<x-app-shell title="School Oversight">
    @php
        $totalClasses = $sections->count();
        $totalLearners = $sections->sum('learners_count');
        $markedCount = count($markedSectionIds);
        $adviserCount = $sections->pluck('adviser.id')->filter()->unique()->count();
    @endphp

    <div class="space-y-6">
        {{-- Read-only banner: set expectations plainly. --}}
        <div class="flex items-start gap-3 rounded-card border border-indigo-200 bg-indigo-50 px-5 py-4 dark:border-indigo-500/30 dark:bg-indigo-500/10">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-500 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">Read-only oversight</p>
                <p class="mt-0.5 text-xs leading-relaxed text-indigo-700/80 dark:text-indigo-300/80">
                    You can view and print every teacher's records in your school, but only advisers can edit their own
                    classes. {{ $activeYear ? 'Showing SY '.$activeYear->name.'.' : 'No active school year is set.' }}
                </p>
            </div>
        </div>

        {{-- Summary stats --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Teachers', $adviserCount, 'text-slate-900 dark:text-white'],
                ['Classes', $totalClasses, 'text-slate-900 dark:text-white'],
                ['Learners', $totalLearners, 'text-slate-900 dark:text-white'],
                ['Marked today', $markedCount.' / '.$totalClasses, $markedCount === $totalClasses && $totalClasses > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'],
            ] as [$label, $value, $tone])
                <div class="rounded-card border border-slate-200/80 bg-white p-4 shadow-soft dark:border-white/10 dark:bg-navy-800">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-extrabold tabular-nums {{ $tone }}">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if ($sections->isEmpty())
            <div class="rounded-card border border-slate-200/80 bg-white p-10 text-center shadow-soft dark:border-white/10 dark:bg-navy-800">
                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No classes to show yet</p>
                <p class="mt-1 text-xs text-slate-500">Classes appear here once your school's advisers set them up for the active year.</p>
            </div>
        @else
            {{-- One card per adviser --}}
            <div class="space-y-4">
                @foreach ($byAdviser as $adviserId => $group)
                    @php $adviser = $group->first()->adviser; @endphp
                    <div class="overflow-hidden rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
                        <div class="flex items-center gap-3 border-b border-slate-200/80 px-5 py-3.5 dark:border-white/10">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white">
                                {{ $adviser ? strtoupper(substr($adviser->first_name, 0, 1).substr($adviser->last_name, 0, 1)) : '—' }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                    {{ $adviser ? $adviser->last_name.', '.$adviser->first_name : 'Unassigned classes' }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $group->count() }} {{ Str::plural('class', $group->count()) }}</p>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($group as $section)
                                @php $marked = in_array($section->id, $markedSectionIds, true); @endphp
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 py-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                                            {{ $section->gradeLevel->name }} — {{ $section->name }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $section->learners_count }} {{ Str::plural('learner', $section->learners_count) }}</p>
                                    </div>

                                    @if ($marked)
                                        <span class="hidden items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 sm:inline-flex">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            Marked today
                                        </span>
                                    @else
                                        <span class="hidden items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500 dark:bg-white/10 dark:text-slate-400 sm:inline-flex">
                                            Not yet marked
                                        </span>
                                    @endif

                                    {{-- Every adviser School Form for this class, one tap each. --}}
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span class="mr-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Forms</span>
                                        @foreach (['sf1' => 'SF1', 'sf2' => 'SF2', 'sf3' => 'SF3', 'sf5' => 'SF5', 'sf8' => 'SF8', 'sf9' => 'SF9'] as $key => $label)
                                            <a href="{{ route("supervisor.{$key}.show", $section) }}" target="_blank"
                                               title="View {{ $label }} for {{ $section->gradeLevel->name }} — {{ $section->name }}"
                                               class="inline-flex items-center rounded-md border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600 transition-colors hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-white/15 dark:text-slate-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                                {{ $label }}
                                            </a>
                                        @endforeach
                                        <a href="{{ route('supervisor.insights.index', ['sy' => $section->school_year_id]) }}"
                                           title="Open the insights dashboard for {{ $section->gradeLevel->name }} — {{ $section->name }}"
                                           class="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-[11px] font-bold text-indigo-600 transition-colors hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                                            Insights
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-shell>
