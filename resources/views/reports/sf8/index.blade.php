<x-app-shell title="SF8 — Health &amp; Nutrition">
    <div class="mx-auto max-w-2xl">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Generate SF8</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    The Learner's Basic Health and Nutrition Report prints your advisory roster with blank
                    Weight, Height, BMI and HFA columns ready for the weighing session — the PDF opens in a new tab.
                </p>
            </div>

            @php
                // The years this teacher has advisory classes in, newest first —
                // the selector that makes previous school years reachable.
                $schoolYears = $sections->pluck('schoolYear')->unique('id')->values();
                $sectionData = $sections->map(fn ($s) => [
                    'id' => $s->id,
                    'sy' => $s->school_year_id,
                    'label' => $s->gradeLevel->name.' — '.$s->name,
                ])->values();
            @endphp
            <form method="GET" action=""
                  x-data="{
                      sy: @js($schoolYears->first()?->id),
                      section: '',
                      sections: @js($sectionData),
                      get filtered() { return this.sections.filter(s => s.sy === this.sy) },
                  }"
                  @submit.prevent="if(section){
                      const q = new URLSearchParams({
                          district: document.getElementById('district').value,
                          track: document.getElementById('track').value,
                          date: document.getElementById('date').value,
                          assessed: document.getElementById('assessed').value,
                          certified: document.getElementById('certified').value,
                          reviewed: document.getElementById('reviewed').value,
                      });
                      window.open('{{ url('reports/sf8') }}/' + section + '?' + q.toString(), '_blank')
                  }">
                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="sy" class="label">School Year</label>
                            <select id="sy" x-model.number="sy" @change="section = ''" class="input">
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}">SY {{ $sy->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">Pick a past year to print its report.</p>
                        </div>
                        <div>
                            <label for="section" class="label">Class <span class="text-brand-500">*</span></label>
                            <select id="section" x-model.number="section" required class="input">
                                <option value="">— Select a class —</option>
                                <template x-for="s in filtered" :key="s.id">
                                    <option :value="s.id" x-text="s.label"></option>
                                </template>
                            </select>
                            @if ($sections->isEmpty())
                                <p class="mt-1 text-[11px] text-amber-500">No advisory classes — SF8 is for your advisory only.</p>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="district" class="label">District <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="district" type="text" maxlength="120" placeholder="e.g. District II" class="input">
                        </div>
                        <div>
                            <label for="track" class="label">Track/Strand <span class="font-normal text-slate-400">(SHS only)</span></label>
                            <input id="track" type="text" maxlength="120" placeholder="e.g. Academic — STEM" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="date" class="label">Date of Assessment <span class="font-normal text-slate-400">(optional — also sets learner ages)</span></label>
                            <input id="date" type="date" class="input">
                        </div>
                        <div>
                            <label for="assessed" class="label">Conducted/Assessed By <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="assessed" type="text" maxlength="120" placeholder="e.g. MARIA B. SANTOS, School Nurse" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="certified" class="label">Certified Correct By <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="certified" type="text" maxlength="120" placeholder="e.g. JUAN A. DELA CRUZ, Class Adviser" class="input">
                        </div>
                        <div>
                            <label for="reviewed" class="label">Reviewed By <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="reviewed" type="text" maxlength="120" placeholder="e.g. ANA C. REYES, Principal I" class="input">
                        </div>
                    </div>

                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-xs leading-relaxed text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
                        The Weight, Height, BMI, HFA and Summary Table cells print blank on purpose — they are
                        measured and tallied by hand during the school's weighing session, as on the official form.
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
