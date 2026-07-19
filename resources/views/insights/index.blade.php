<x-app-shell title="Advanced Reports">
    <div class="mx-auto max-w-2xl">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Advanced Reports</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Class insight dashboards built from your attendance, book, and promotion records —
                    see who is slipping before the SF2 makes it official.
                </p>
            </div>

            @php $schoolYears = $sections->pluck('schoolYear')->unique('id')->values(); @endphp
            <div class="p-6" x-data="{ sy: @js($schoolYears->first()?->id) }">
                @if ($schoolYears->count() > 1)
                    <div class="mb-4 flex flex-wrap gap-1.5">
                        @foreach ($schoolYears as $sy)
                            <button type="button" @click="sy = {{ $sy->id }}"
                                    class="cursor-pointer rounded-full px-3 py-1 text-xs font-semibold transition-colors"
                                    :class="sy === {{ $sy->id }}
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300 dark:hover:bg-white/15'">
                                SY {{ $sy->name }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @forelse ($sections as $s)
                    <div x-show="sy === {{ $s->school_year_id }}"
                         class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 py-3 last:border-0 dark:border-white/5">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $s->gradeLevel->name }} — {{ $s->name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">SY {{ $s->schoolYear->name }}</p>
                        </div>
                        <a href="{{ route('insights.show', $s) }}"
                           class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                            Open dashboard
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        No advisory classes — insights cover your own advisory only.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-shell>
