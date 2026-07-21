<x-app-shell title="SF9 — Learner's Progress Report Card">
    @php
        $bothHs = $school?->offersBothHighSchool() ?? false;
        $jhsCount = $sections->filter(fn ($s) => (int) ($s->gradeLevel->level_order ?? 0) >= 7 && (int) ($s->gradeLevel->level_order ?? 0) < 11)->count();
        $shsCount = $sections->filter(fn ($s) => (int) ($s->gradeLevel->level_order ?? 0) >= 11)->count();
    @endphp
    <div class="mx-auto max-w-2xl space-y-5" x-data="{ level: '{{ $bothHs ? 'jhs' : 'all' }}' }">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">SF9 — Report Card</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Enter grades and core values for your advisory class, then open the locked PDF report card.
                </p>

                {{-- A JHS + SHS campus generates two SF9 variants, so let the adviser pick which. --}}
                @if ($bothHs)
                    <div class="mt-3">
                        <span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Report card type</span>
                        <div class="inline-flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-white/10 dark:bg-white/5">
                            <button type="button" @click="level = 'jhs'"
                                    :class="level === 'jhs' ? 'bg-white text-brand-600 shadow-sm dark:bg-navy-700 dark:text-brand-300' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'"
                                    class="cursor-pointer rounded-lg px-4 py-1.5 text-xs font-semibold transition-colors">SF9 — JHS</button>
                            <button type="button" @click="level = 'shs'"
                                    :class="level === 'shs' ? 'bg-white text-brand-600 shadow-sm dark:bg-navy-700 dark:text-brand-300' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'"
                                    class="cursor-pointer rounded-lg px-4 py-1.5 text-xs font-semibold transition-colors">SF9 — SHS</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($sections as $section)
                    @php
                        $noAreas = ($section->learning_areas_count ?? 0) === 0;
                        $order = (int) ($section->gradeLevel->level_order ?? 0);
                        $secLevel = $order >= 11 ? 'shs' : ($order >= 7 ? 'jhs' : 'es');
                        $levelLabel = $order >= 11 ? 'Senior High (semestral)' : ($order >= 7 ? 'Junior High (4 quarters)' : 'Elementary (4 quarters)');
                    @endphp
                    <div x-show="level === 'all' || level === '{{ $secLevel }}'"
                         class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
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
                            <a href="{{ route('reports.sf9.show', $section) }}" target="_blank" class="btn-primary btn-sm">Open PDF</a>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-6 text-sm text-amber-500">No advisory classes — SF9 is generated for the class you advise.</p>
                @endforelse

                {{-- Shown only when the active JHS/SHS filter hides every class. --}}
                @if ($bothHs && $sections->isNotEmpty())
                    <p x-show="(level === 'jhs' && {{ $jhsCount }} === 0) || (level === 'shs' && {{ $shsCount }} === 0)" x-cloak
                       class="px-6 py-6 text-sm text-slate-400">No <span x-text="level.toUpperCase()"></span> advisory classes.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-shell>
