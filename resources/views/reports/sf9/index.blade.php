<x-app-shell title="SF9 — Learner's Progress Report Card">
    <div class="mx-auto max-w-2xl space-y-5">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">SF9 — Report Card</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Enter grades and core values for your advisory class, then open the locked PDF report card.
                </p>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($sections as $section)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                {{ $section->gradeLevel->name }} — {{ $section->name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                SY {{ $section->schoolYear->name }}
                                · {{ ($section->gradeLevel->level_order ?? 0) >= 11 ? 'Senior High (semestral)' : 'Junior High (4 quarters)' }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ route('teacher.sf9.grades', $section) }}" class="btn-secondary btn-sm">Enter grades</a>
                            <a href="{{ route('reports.sf9.show', $section) }}" target="_blank" class="btn-primary btn-sm">Open PDF</a>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-6 text-sm text-amber-500">No advisory classes — SF9 is generated for the class you advise.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-shell>
