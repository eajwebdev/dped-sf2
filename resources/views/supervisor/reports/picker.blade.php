<x-app-shell :title="$title">
    <div class="mx-auto max-w-2xl">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Read-only oversight — {{ $subtitle }} The PDF opens in a new tab.
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
                <form method="GET" action=""
                      x-data="{
                          sy: @js($schoolYears->first()?->id),
                          section: '',
                          sections: @js($sectionData),
                          get filtered() { return this.sections.filter(s => s.sy === this.sy) },
                      }"
                      @submit.prevent="if(section){ window.open('{{ url('oversight/'.$form) }}/' + section + '?head=' + encodeURIComponent(document.getElementById('head').value) + '&district=' + encodeURIComponent(document.getElementById('district').value), '_blank') }">
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

                        <div>
                            <label for="district" class="label">District <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="district" type="text" maxlength="120" placeholder="e.g. District II" class="input">
                        </div>

                        <div>
                            <label for="head" class="label">School Head <span class="font-normal text-slate-400">(optional — printed in the signatory block)</span></label>
                            <input id="head" type="text" maxlength="120" placeholder="e.g. JUAN A. DELA CRUZ, Principal I" class="input">
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-slate-200/80 px-6 py-4 dark:border-white/10">
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-show="!section">Choose a class to enable the report.</p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400" x-show="section" x-cloak>Ready — opens in a new tab.</p>
                        <button type="submit" class="btn-primary btn-md shrink-0" :disabled="!section">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            View Report
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-shell>
