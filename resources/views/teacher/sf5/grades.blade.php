<x-app-shell :title="null" wide>
    {{-- Header --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('reports.sf5.index') }}" class="text-xs text-gray-400 hover:text-gray-600">&larr; SF5 classes</a>
            <h1 class="text-lg font-semibold">General Averages — {{ $section->gradeLevel->name }} {{ $section->name }}</h1>
            <p class="text-xs text-gray-400">
                SY {{ $section->schoolYear->name }} · {{ $roster->count() }} learners ·
                The action taken comes from the promotion status; tick Irregular for promoted learners still carrying incomplete subjects.
            </p>
        </div>
        <a href="{{ route('reports.sf5.show', $section) }}" target="_blank"
           class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Generate SF5</a>
    </div>

    @if (session('success'))
        <div class="mb-3 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-3 rounded-lg border border-red-300 bg-red-50 px-4 py-2.5 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
            Check the highlighted rows — averages must be between 60 and 100.
        </div>
    @endif

    <form method="POST" action="{{ route('reports.sf5.grades.save', $section) }}">
        @csrf
        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-white/10 dark:bg-navy-800">
            <table class="w-full text-xs">
                <thead class="border-b border-gray-200 bg-gray-50 text-left dark:border-white/10 dark:bg-navy-800/60">
                    <tr class="text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-3 py-2.5">Learner</th>
                        <th class="px-2 py-2.5">Status</th>
                        <th class="px-2 py-2.5">General average</th>
                        <th class="px-2 py-2.5 text-center">Irregular</th>
                        <th class="px-2 py-2.5">Subjects completed <span class="font-normal normal-case">(as of EoSY)</span></th>
                        <th class="px-2 py-2.5">Subjects incomplete <span class="font-normal normal-case">(as of EoSY)</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($roster as $i => $enrollment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30 {{ $errors->has("rows.{$i}.general_average") ? 'bg-red-50 dark:bg-red-500/10' : '' }}">
                            <td class="whitespace-nowrap px-3 py-1.5 font-medium">
                                {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                                <span class="ml-1 text-[10px] text-gray-400">{{ $enrollment->student->gender === 'Male' ? 'M' : 'F' }}</span>
                                <input type="hidden" name="rows[{{ $i }}][enrollment_id]" value="{{ $enrollment->id }}">
                            </td>
                            <td class="whitespace-nowrap px-2 py-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $service->action($enrollment) ?: strtoupper($enrollment->promotion_status ?? '—') }}
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="number" name="rows[{{ $i }}][general_average]" step="0.001" min="60" max="100"
                                       value="{{ old("rows.{$i}.general_average", $enrollment->general_average !== null ? rtrim(rtrim((string) $enrollment->general_average, '0'), '.') : '') }}"
                                       placeholder="—"
                                       class="w-24 rounded-lg border-gray-300 py-1 text-xs dark:border-white/15 dark:bg-navy-900">
                            </td>
                            <td class="px-2 py-1.5 text-center">
                                <input type="hidden" name="rows[{{ $i }}][is_irregular]" value="0">
                                <input type="checkbox" name="rows[{{ $i }}][is_irregular]" value="1"
                                       @checked(old("rows.{$i}.is_irregular", $enrollment->is_irregular))
                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-white/20 dark:bg-navy-900">
                            </td>
                            <td class="px-2 py-1.5">
                                <input name="rows[{{ $i }}][subjects_completed]" maxlength="255"
                                       value="{{ old("rows.{$i}.subjects_completed", $enrollment->subjects_completed) }}"
                                       class="w-full min-w-36 rounded-lg border-gray-300 py-1 text-xs dark:border-white/15 dark:bg-navy-900">
                            </td>
                            <td class="px-2 py-1.5">
                                <input name="rows[{{ $i }}][subjects_incomplete]" maxlength="255"
                                       value="{{ old("rows.{$i}.subjects_incomplete", $enrollment->subjects_incomplete) }}"
                                       class="w-full min-w-36 rounded-lg border-gray-300 py-1 text-xs dark:border-white/15 dark:bg-navy-900">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between gap-3">
            <p class="text-[11px] text-gray-400">
                Proficiency bands: B ≤74 · D 75–79 · AP 80–84 · P 85–89 · A ≥90.
                Averages of 90+ print to 3 decimals (honor learners), others to 2.
            </p>
            <button class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                Save all
            </button>
        </div>
    </form>
</x-app-shell>
