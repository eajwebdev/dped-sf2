<x-app-shell title="SF10 — Learner Permanent Academic Record">
    <div class="mx-auto max-w-2xl space-y-5">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">SF10 — Permanent Academic Record</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    The learner's permanent record (formerly Form 137). The scholastic record is built from the
                    same quarterly ratings you enter for SF9 — one locked PDF record per learner.
                </p>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($sections as $section)
                    @php
                        $noAreas = ($section->learning_areas_count ?? 0) === 0;
                        $order = (int) ($section->gradeLevel->level_order ?? 0);
                        $levelLabel = $order >= 11 ? 'Senior High' : ($order >= 7 ? 'Junior High' : 'Elementary');
                    @endphp
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                {{ $section->gradeLevel->name }} — {{ $section->name }}
                            </p>
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                <span>SY {{ $section->schoolYear->name }}</span>
                                <span>· {{ $levelLabel }}</span>
                                <span>· {{ $section->learners_count }} {{ Str::plural('learner', $section->learners_count) }}</span>
                                @if ($noAreas)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">No learning areas yet</span>
                                @else
                                    <span>· {{ $section->learning_areas_count }} {{ Str::plural('learning area', $section->learning_areas_count) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ route('teacher.sf9.grades', $section) }}" class="btn-secondary btn-sm">
                                {{ $noAreas ? 'Set up & enter grades' : 'Enter grades' }}
                            </a>
                            <a href="{{ route('reports.sf10.show', $section) }}" target="_blank" class="btn-primary btn-sm">Open PDF</a>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-6 text-sm text-amber-500">No advisory classes — SF10 is generated for the class you advise.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-shell>
