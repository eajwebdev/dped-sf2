<x-app-shell title="Move my class up">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('teacher.dashboard') }}" class="text-sm font-medium text-slate-500 transition-colors hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Move my class up</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Pick who advances from your previous advisory — they move into your class in SY {{ $activeYear?->name ?? '—' }}, and you stay their adviser.
            </p>
        </div>

        @if ($sources->isEmpty())
            <x-card>
                <x-empty-state title="Nothing to move up"
                               description="You have no advisory class from a previous school year with learners still in it."
                               icon="M4.5 10.5 12 3m0 0l7.5 7.5M12 3v18" />
            </x-card>
        @else
            {{-- Source picker (only when the teacher advised several old classes) --}}
            @if ($sources->count() > 1)
                <form method="GET" action="{{ route('teacher.promotion.index') }}">
                    <label for="section_id" class="label">From class</label>
                    <select id="section_id" name="section_id" onchange="this.form.submit()" class="input">
                        @foreach ($sources as $s)
                            <option value="{{ $s->id }}" @selected($source && $s->id === $source->id)>
                                SY {{ $s->schoolYear->name }} · {{ $s->gradeLevel->name }} — {{ $s->name }} ({{ $s->learners_count }} learners)
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            @if ($source)
                <form method="POST" action="{{ route('teacher.promotion.promote') }}"
                      x-data="{ checked: {{ $learners->reject(fn ($l) => $l['alreadyMoved'])->count() }} }">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $source->id }}">

                    <x-card :padding="false">
                        <x-slot:title>
                            SY {{ $source->schoolYear->name }} · {{ $source->gradeLevel->name }} — {{ $source->name }}
                        </x-slot:title>
                        <x-slot:actions>
                            <span class="badge bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300" x-text="checked + ' selected'"></span>
                        </x-slot:actions>

                        <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto dark:divide-white/5">
                            @foreach ($learners as $row)
                                <label class="flex items-center gap-3 px-5 py-2.5 {{ $row['alreadyMoved'] ? 'opacity-50' : 'cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5' }}">
                                    <input type="checkbox" name="enrollment_ids[]" value="{{ $row['enrollment']->id }}"
                                           @if($row['alreadyMoved']) disabled @else checked @endif
                                           @change="checked += $event.target.checked ? 1 : -1"
                                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-white/20 dark:bg-navy-900">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-slate-900 dark:text-white">{{ $row['enrollment']->student->full_name }}</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $row['enrollment']->student->gender }} · LRN {{ $row['enrollment']->student->lrn }}</span>
                                    </span>
                                    @if ($row['alreadyMoved'])
                                        <span class="badge bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Already in SY {{ $activeYear->name }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        <div class="space-y-4 border-t border-slate-100 p-5 dark:border-white/5">
                            @if ($source->gradeLevel->is_graduating)
                                <p class="text-sm text-amber-600 dark:text-amber-400">
                                    {{ $source->gradeLevel->name }} is the graduating grade — selected learners will be marked <b>graduated</b>.
                                </p>
                                <input type="hidden" name="target_name" value="{{ $source->name }}">
                            @else
                                <div x-data="{ target: '{{ old('target_section_id', '') }}' }">
                                    <label for="target_section_id" class="label">
                                        They move into {{ $nextGrade?->name }} … <span class="font-normal text-slate-400">(the class picked here decides their new adviser)</span>
                                    </label>
                                    <select id="target_section_id" name="target_section_id" x-model="target" class="input">
                                        <option value="">
                                            My class — {{ $existingTarget ? $nextGrade->name.' — '.$existingTarget->name : 'create it, I stay their adviser' }}
                                        </option>
                                        @foreach ($targetOptions as $opt)
                                            <option value="{{ $opt->id }}">
                                                {{ $nextGrade->name }} — {{ $opt->name }} · adviser: {{ $opt->adviser?->full_name ?? 'none yet' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('target_section_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror

                                    {{-- Only needed when creating the teacher's own class --}}
                                    @if (! $existingTarget)
                                        <div x-show="target === ''" class="mt-3">
                                            <label for="target_name" class="label">Name for your new class</label>
                                            <input id="target_name" name="target_name" value="{{ old('target_name', $source->name) }}" maxlength="50"
                                                   :required="target === ''" class="input" placeholder="Class name">
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Created in SY {{ $activeYear->name }} with you as its adviser.</p>
                                            @error('target_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                        </div>
                                    @else
                                        <input type="hidden" name="target_name" value="{{ $existingTarget->name }}">
                                    @endif
                                </div>
                            @endif

                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary btn-md" :disabled="checked === 0"
                                        onclick="return confirm('Move the selected learners up? Their previous records stay untouched.')">
                                    {{ $source->gradeLevel->is_graduating ? 'Mark selected as graduated' : 'Move selected up' }}
                                </button>
                            </div>
                        </div>
                    </x-card>
                </form>
            @endif
        @endif
    </div>
</x-app-shell>
