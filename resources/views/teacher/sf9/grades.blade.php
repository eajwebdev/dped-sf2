<x-app-shell title="SF9 Grades — {{ $section->gradeLevel->name }} {{ $section->name }}" :wide="true">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Enter each learner's grades and observed-values marks. These fill the locked SF9 report card.
                </p>
                <p class="mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                    {{ $isShs ? 'Senior High — semestral' : 'Junior High — four quarters' }}
                    · passing grade {{ $passingGrade }}
                </p>
            </div>
            <a href="{{ route('reports.sf9.show', $section) }}" target="_blank" class="btn-primary btn-sm shrink-0">Open SF9 PDF</a>
        </div>

        {{-- Learning areas management --}}
        <div class="rounded-card border border-slate-200/80 bg-white p-5 shadow-soft dark:border-white/10 dark:bg-navy-800">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Learning Areas</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">The subjects that appear on the report card for this class.</p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                @forelse ($assignments as $a)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 py-1.5 pl-3 pr-1.5 text-xs font-semibold text-slate-700 dark:bg-white/5 dark:text-slate-200">
                        {{ $a->subject->name }}
                        <form method="POST" action="{{ route('teacher.sf9.subjects.destroy', $a) }}"
                              onsubmit="return confirm('Remove {{ $a->subject->name }} from this report card?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" title="Remove" class="flex h-4 w-4 items-center justify-center rounded-full text-slate-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-500/20">&times;</button>
                        </form>
                    </span>
                @empty
                    <span class="text-xs text-amber-500">No learning areas yet — add them below.</span>
                @endforelse
            </div>

            <div class="mt-4 flex flex-wrap items-end gap-3">
                <form method="POST" action="{{ route('teacher.sf9.subjects.store', $section) }}" class="flex items-end gap-2">
                    @csrf
                    <div>
                        <label class="label">Add learning area</label>
                        <input type="text" name="name" required maxlength="100" placeholder="e.g. Mathematics" class="input">
                    </div>
                    <button type="submit" class="btn-secondary btn-md">Add</button>
                </form>
                @unless ($isShs)
                    <form method="POST" action="{{ route('teacher.sf9.subjects.standard', $section) }}">
                        @csrf
                        <button type="submit" class="btn-ghost btn-md">Add standard JHS areas</button>
                    </form>
                @endunless
            </div>
        </div>

        @if ($roster->isEmpty())
            <div class="rounded-card border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                No active learners in this class.
            </div>
        @else
            <form method="POST" action="{{ route('teacher.sf9.grades.save', $section) }}"
                  x-data="{ tab: 'grades', subject: {{ $assignments->first()?->subject->id ?? 'null' }}, core: '{{ array_key_first($coreValues) }}' }"
                  class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
                @csrf

                <div class="flex items-center gap-1 border-b border-slate-200/80 px-4 pt-3 dark:border-white/10">
                    <button type="button" @click="tab = 'grades'"
                            :class="tab === 'grades' ? 'border-brand-500 text-brand-600 dark:text-brand-300' : 'border-transparent text-slate-500'"
                            class="cursor-pointer border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">Grades</button>
                    <button type="button" @click="tab = 'values'"
                            :class="tab === 'values' ? 'border-brand-500 text-brand-600 dark:text-brand-300' : 'border-transparent text-slate-500'"
                            class="cursor-pointer border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">Observed Values</button>
                </div>

                {{-- ================= Grades tab ================= --}}
                <div x-show="tab === 'grades'" class="p-5">
                    @if ($assignments->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">Add a learning area first.</p>
                    @else
                        {{-- Learning-area pills: one click to switch, no dropdown hunting --}}
                        <div class="mb-4 flex flex-wrap gap-1.5">
                            @foreach ($assignments as $a)
                                <button type="button" @click="subject = {{ $a->subject->id }}"
                                        :class="subject === {{ $a->subject->id }}
                                            ? 'bg-brand-500 text-white shadow-sm'
                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10'"
                                        class="cursor-pointer rounded-full px-3.5 py-1.5 text-xs font-semibold transition-colors">
                                    {{ $a->subject->name }}
                                </button>
                            @endforeach
                        </div>

                        @foreach ($assignments as $a)
                            @php ($s = $a->subject)
                            <div x-show="subject === {{ $s->id }}" class="overflow-x-auto">
                                <table class="w-full min-w-[680px] text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:border-white/10">
                                            <th class="py-2 pr-3">Learner</th>
                                            @foreach ($periodLabels as $p => $label)
                                                <th class="px-2 py-2 text-center">{{ $label }}</th>
                                            @endforeach
                                            <th class="px-2 py-2 text-center">{{ $isShs ? '1st / 2nd Sem' : 'Final' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                        @foreach ($roster as $e)
                                            {{-- Self-contained x-data: no external script, so it can never fail to
                                                 load. Final shows only once all four quarters are entered. --}}
                                            <tr x-data="{
                                                    q1: '{{ data_get($existingGrades, "$e->id.$s->id.1") }}',
                                                    q2: '{{ data_get($existingGrades, "$e->id.$s->id.2") }}',
                                                    q3: '{{ data_get($existingGrades, "$e->id.$s->id.3") }}',
                                                    q4: '{{ data_get($existingGrades, "$e->id.$s->id.4") }}',
                                                    n(v) { const x = parseFloat(v); return isNaN(x) ? null : x; },
                                                    get final() {
                                                        const v = [this.q1, this.q2, this.q3, this.q4].map(x => this.n(x));
                                                        return v.some(x => x === null) ? '' : Math.round((v[0] + v[1] + v[2] + v[3]) / 4);
                                                    },
                                                    get sem1() { const a = this.n(this.q1), b = this.n(this.q2); return (a === null || b === null) ? '' : Math.round((a + b) / 2); },
                                                    get sem2() { const a = this.n(this.q3), b = this.n(this.q4); return (a === null || b === null) ? '' : Math.round((a + b) / 2); }
                                                }">
                                                <td class="py-1.5 pr-3 text-slate-700 dark:text-slate-200">{{ $e->student->full_name }}</td>
                                                @foreach ([1,2,3,4] as $p)
                                                    <td class="px-2 py-1.5 text-center">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="grades[{{ $e->id }}][{{ $s->id }}][{{ $p }}]"
                                                               x-model="q{{ $p }}"
                                                               class="input w-20 text-center">
                                                    </td>
                                                @endforeach
                                                <td class="px-2 py-1.5 text-center">
                                                    @if ($isShs)
                                                        <span class="font-bold tabular-nums" x-text="sem1 === '' ? '—' : sem1"></span>
                                                        <span class="text-slate-400"> / </span>
                                                        <span class="font-bold tabular-nums" x-text="sem2 === '' ? '—' : sem2"></span>
                                                    @else
                                                        <span class="font-bold tabular-nums"
                                                              :class="final === '' ? 'text-slate-400' : (final >= {{ $passingGrade }} ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400')"
                                                              x-text="final === '' ? '—' : final"></span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                        <p class="mt-3 text-[11px] text-slate-400">The Final rating appears automatically once all four quarters are entered — the printed SF9 recomputes it from what you save.</p>
                    @endif
                </div>

                {{-- ================= Observed Values tab ================= --}}
                <div x-show="tab === 'values'" x-cloak class="p-5">
                    {{-- Core-value pills --}}
                    <div class="mb-4 flex flex-wrap gap-1.5">
                        @foreach ($coreValues as $key => $label)
                            <button type="button" @click="core = '{{ $key }}'"
                                    :class="core === '{{ $key }}'
                                        ? 'bg-brand-500 text-white shadow-sm'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10'"
                                    class="cursor-pointer rounded-full px-3.5 py-1.5 text-xs font-semibold transition-colors">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    @foreach ($coreValues as $key => $label)
                        <div x-show="core === '{{ $key }}'" class="space-y-6">
                            @foreach ($behaviors[$key] ?? [] as $i => $statement)
                                @php ($behavior = $i + 1)
                                {{-- Each behaviour statement is its own quick-fillable grid --}}
                                <div x-data class="rounded-xl border border-slate-200/70 dark:border-white/10">
                                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200/70 px-4 py-2.5 dark:border-white/10">
                                        <p class="max-w-2xl text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $statement }}</p>
                                        <div class="flex shrink-0 items-center gap-1">
                                            <span class="mr-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Set all</span>
                                            @foreach ($marks as $mv => $ml)
                                                <button type="button"
                                                        @click="$root.querySelectorAll('select').forEach(s => s.value = '{{ $mv }}')"
                                                        title="Set every cell to {{ $ml }}"
                                                        class="cursor-pointer rounded-md border border-slate-200 px-1.5 py-0.5 text-[11px] font-bold text-slate-600 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-white/15 dark:text-slate-300 dark:hover:bg-brand-500/10">{{ $mv }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full min-w-[640px] text-sm">
                                            <thead>
                                                <tr class="border-b border-slate-200 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:border-white/10">
                                                    <th class="py-2 pl-4 pr-3">Learner</th>
                                                    @foreach ($periodLabels as $p => $plabel)
                                                        <th class="px-2 py-2 text-center">{{ $plabel }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                                @foreach ($roster as $e)
                                                    <tr>
                                                        <td class="py-1.5 pl-4 pr-3 text-slate-700 dark:text-slate-200">{{ $e->student->full_name }}</td>
                                                        @foreach ([1,2,3,4] as $p)
                                                            <td class="px-2 py-1.5 text-center">
                                                                <select name="values[{{ $e->id }}][{{ $key }}][{{ $behavior }}][{{ $p }}]" class="input w-20 text-center">
                                                                    <option value="">—</option>
                                                                    @foreach ($marks as $mv => $ml)
                                                                        <option value="{{ $mv }}" @selected(data_get($existingValues, "$e->id.$key.$behavior.$p") === $mv)>{{ $mv }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                    <p class="mt-4 text-[11px] text-slate-400">AO = Always Observed · SO = Sometimes Observed · RO = Rarely Observed · NO = Not Observed</p>
                </div>

                <div class="sticky bottom-0 flex justify-end gap-3 border-t border-slate-200/80 bg-white/95 px-5 py-4 backdrop-blur dark:border-white/10 dark:bg-navy-800/95">
                    <button type="submit" class="btn-primary btn-md">Save Grades &amp; Values</button>
                </div>
            </form>
        @endif
    </div>
</x-app-shell>
