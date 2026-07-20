<x-app-shell title="SF9 Grades — {{ $section->gradeLevel->name }} {{ $section->name }}" :wide="true">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Enter each learner's grades and core-values marks. These fill the locked SF9 report card.
                <span class="font-semibold">{{ $isShs ? 'Senior High (semestral)' : 'Junior High (4 quarters)' }}</span>
            </p>
            <a href="{{ route('reports.sf9.show', $section) }}" target="_blank"
               class="btn-primary btn-sm shrink-0">Open SF9 PDF</a>
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
                            class="cursor-pointer border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">Core Values</button>
                </div>

                {{-- ===== Grades tab ===== --}}
                <div x-show="tab === 'grades'" class="p-5">
                    @if ($assignments->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">Add a learning area first.</p>
                    @else
                        <label class="label">Learning area</label>
                        <select x-model.number="subject" class="input mb-4 max-w-xs">
                            @foreach ($assignments as $a)
                                <option value="{{ $a->subject->id }}">{{ $a->subject->name }}</option>
                            @endforeach
                        </select>

                        @foreach ($assignments as $a)
                            @php ($s = $a->subject)
                            <div x-show="subject === {{ $s->id }}" class="overflow-x-auto">
                                <table class="w-full min-w-[640px] text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:border-white/10">
                                            <th class="py-2 pr-3">Learner</th>
                                            @foreach ($periodLabels as $p => $label)
                                                <th class="px-2 py-2 text-center">{{ $label }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                        @foreach ($roster as $e)
                                            <tr>
                                                <td class="py-1.5 pr-3 text-slate-700 dark:text-slate-200">{{ $e->student->full_name }}</td>
                                                @foreach ($periodLabels as $p => $label)
                                                    <td class="px-2 py-1.5 text-center">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="grades[{{ $e->id }}][{{ $s->id }}][{{ $p }}]"
                                                               value="{{ data_get($existingGrades, "$e->id.$s->id.$p") }}"
                                                               class="input w-20 text-center">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- ===== Core values tab ===== --}}
                <div x-show="tab === 'values'" x-cloak class="p-5">
                    <label class="label">Core value</label>
                    <select x-model="core" class="input mb-4 max-w-xs">
                        @foreach ($coreValues as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    @foreach ($coreValues as $key => $label)
                        <div x-show="core === '{{ $key }}'" class="overflow-x-auto">
                            <table class="w-full min-w-[640px] text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:border-white/10">
                                        <th class="py-2 pr-3">Learner</th>
                                        @foreach ($periodLabels as $p => $plabel)
                                            <th class="px-2 py-2 text-center">{{ $plabel }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    @foreach ($roster as $e)
                                        <tr>
                                            <td class="py-1.5 pr-3 text-slate-700 dark:text-slate-200">{{ $e->student->full_name }}</td>
                                            @foreach ($periodLabels as $p => $plabel)
                                                <td class="px-2 py-1.5 text-center">
                                                    <select name="values[{{ $e->id }}][{{ $key }}][{{ $p }}]" class="input w-24 text-center">
                                                        <option value="">—</option>
                                                        @foreach ($marks as $mv => $ml)
                                                            <option value="{{ $mv }}" @selected(data_get($existingValues, "$e->id.$key.$p") === $mv)>{{ $mv }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                    <p class="mt-3 text-[11px] text-slate-400">AO = Always Observed · SO = Sometimes · RO = Rarely · NO = Not Observed</p>
                </div>

                <div class="flex justify-end border-t border-slate-200/80 px-5 py-4 dark:border-white/10">
                    <button type="submit" class="btn-primary btn-md">Save Grades &amp; Values</button>
                </div>
            </form>
        @endif
    </div>
</x-app-shell>
