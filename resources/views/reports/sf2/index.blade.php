<x-app-shell title="SF2 — Daily Attendance Report">
    <div class="mx-auto max-w-2xl">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Generate SF2</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Pick a class and a month — the PDF opens in a new tab.</p>
            </div>

            @php
                $schoolYears = $sections->pluck('schoolYear')->unique('id')->values();
                $sectionData = $sections->map(fn ($s) => [
                    'id' => $s->id,
                    'sy' => $s->school_year_id,
                    'startYear' => $s->schoolYear->start_date->year,
                    'advisory' => (bool) $s->is_advisory,
                    'label' => $s->gradeLevel->name.' — '.$s->name,
                ])->values();
            @endphp
            <form method="GET" action=""
                  x-data="{
                      sy: @js($schoolYears->first()?->id),
                      mode: 'advisory',
                      section: '',
                      sections: @js($sectionData),
                      get filtered() { return this.sections.filter(s => s.sy === this.sy && s.advisory === (this.mode === 'advisory')) },
                      /* Switching year or report type invalidates the picked class. */
                      syncYear() {
                          this.section = '';
                          const first = this.filtered[0];
                          if (first) document.getElementById('year').value = first.startYear;
                      },
                  }"
                  @submit.prevent="if(section){ window.open('{{ url('reports/sf2') }}/' + section + '?year=' + document.getElementById('year').value + '&month=' + document.getElementById('month').value + '&head=' + encodeURIComponent(document.getElementById('head').value), '_blank') }">
                <div class="space-y-5 p-6">
                    <div>
                        <span class="label">Report Type</span>
                        <div class="grid grid-cols-2 gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-white/10 dark:bg-white/5">
                            <button type="button" @click="mode = 'advisory'; section = ''"
                                    :class="mode === 'advisory' ? 'bg-white text-brand-600 shadow-sm dark:bg-navy-700 dark:text-brand-300' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                                    class="cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold transition-colors">
                                Advisory
                            </button>
                            <button type="button" @click="mode = 'nonadvisory'; section = ''"
                                    :class="mode === 'nonadvisory' ? 'bg-white text-brand-600 shadow-sm dark:bg-navy-700 dark:text-brand-300' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                                    class="cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold transition-colors">
                                Non-Advisory
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400"
                           x-text="mode === 'advisory' ? 'Classes you advise.' : 'Classes you teach a subject in but do not advise.'"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="sy" class="label">School Year</label>
                            <select id="sy" x-model.number="sy" @change="syncYear()" class="input">
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}">SY {{ $sy->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">Pick a past year to print its attendance.</p>
                        </div>
                        <div>
                            <label for="section" class="label">Class <span class="text-brand-500">*</span></label>
                            <select id="section" x-model.number="section" required class="input">
                                <option value="">— Select a class —</option>
                                <template x-for="s in filtered" :key="s.id">
                                    <option :value="s.id" x-text="s.label"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-[11px] text-amber-500" x-show="filtered.length === 0" x-cloak
                               x-text="mode === 'advisory' ? 'No advisory classes for this school year.' : 'No non-advisory classes — no subject assignments outside your advisory.'"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="month" class="label">Month</label>
                            <select id="month" class="input">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected($m === $month)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="year" class="label">Year</label>
                            <input id="year" type="number" value="{{ $year }}" min="2000" max="2100" class="input">
                        </div>
                    </div>

                    <div>
                        <label for="head" class="label">School Head <span class="font-normal text-slate-400">(optional — printed under "Attested by")</span></label>
                        <input id="head" type="text" maxlength="120" placeholder="e.g. JUAN A. DELA CRUZ, Principal I" class="input">
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-slate-200/80 px-6 py-4 dark:border-white/10">
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-show="!section">Choose a class to enable the report.</p>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400" x-show="section" x-cloak>Ready — opens in a new tab.</p>
                    <button type="submit" class="btn-primary btn-md shrink-0" :disabled="!section">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-shell>
