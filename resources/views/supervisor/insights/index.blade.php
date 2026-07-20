<x-app-shell title="Advanced Reports — Class Insights">
    <div class="mx-auto max-w-2xl">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Open a class dashboard</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Read-only oversight — attendance trends, watchlists, textbook status, and proficiency for any class in your school.
                </p>
            </div>

            @php
                $schoolYears = $sections->pluck('schoolYear')->unique('id')->sortByDesc('id')->values();
                $sectionData = $sections->map(fn ($s) => [
                    'id' => $s->id,
                    'sy' => $s->school_year_id,
                    'label' => $s->gradeLevel->name.' — '.$s->name
                        .($s->adviser ? '  ·  '.$s->adviser->last_name.', '.$s->adviser->first_name : '  ·  No adviser'),
                ])->values();
            @endphp

            @if ($sections->isEmpty())
                <div class="p-6">
                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                        No classes exist in your school yet. They will appear here once your advisers set them up.
                    </p>
                </div>
            @else
                <form action=""
                      x-data="{
                          sy: @js($schoolYears->first()?->id),
                          section: '',
                          sections: @js($sectionData),
                          get filtered() { return this.sections.filter(s => s.sy === this.sy) },
                      }"
                      @submit.prevent="if(section){ window.location = '{{ url('oversight/insights') }}/' + section }">
                    <div class="space-y-5 p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="sy" class="label">School Year</label>
                                <select id="sy" x-model.number="sy" @change="section = ''" class="input">
                                    @foreach ($schoolYears as $sy)
                                        <option value="{{ $sy->id }}">SY {{ $sy->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="section" class="label">Class <span class="text-brand-500">*</span></label>
                                <select id="section" x-model.number="section" required class="input">
                                    <option value="">— Select a class —</option>
                                    <template x-for="s in filtered" :key="s.id">
                                        <option :value="s.id" x-text="s.label"></option>
                                    </template>
                                </select>
                                <p class="mt-1 text-[11px] text-amber-500" x-show="filtered.length === 0" x-cloak>
                                    No classes for this school year.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-slate-200/80 px-6 py-4 dark:border-white/10">
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-show="!section">Choose a class to open its dashboard.</p>
                        <button type="submit" class="btn-primary btn-md shrink-0" :disabled="!section">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                            Open Dashboard
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-shell>
