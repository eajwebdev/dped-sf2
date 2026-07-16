<x-app-shell title="Cutting classes">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <a href="{{ route('teacher.dashboard') }}" class="text-sm font-medium text-slate-500 transition-colors hover:text-brand-600 dark:text-slate-400">&larr; Dashboard</a>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Who cut classes</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Advisory learners seen in one period but missing from another — including periods taught by other teachers.
                </p>
            </div>
            <form method="GET" action="{{ route('teacher.cutting.index') }}">
                <label for="date" class="label">Date</label>
                <input id="date" type="date" name="date" value="{{ $date->toDateString() }}"
                       onchange="this.form.submit()" class="input">
            </form>
        </div>

        <x-card :padding="false">
            <x-slot:title>{{ $date->format('l, M d, Y') }}</x-slot:title>
            <x-slot:actions>
                <span class="badge {{ $rows->isEmpty() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' }}">
                    {{ $rows->count() }} {{ Str::plural('learner', $rows->count()) }}
                </span>
            </x-slot:actions>

            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($rows as $row)
                    <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $row['student']->full_name }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                {{ $row['section']->gradeLevel?->name }} — {{ $row['section']->name }} · LRN {{ $row['student']->lrn }}
                            </p>

                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <span class="text-xs font-bold uppercase tracking-wider text-red-500">Skipped</span>
                                @foreach ($row['skipped'] as $s)
                                    <span class="badge bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300">
                                        {{ $s->subject?->name ?? 'Homeroom' }}
                                        @if ($s->teacherSchedule?->time_range)
                                            · <span class="tabular-nums">{{ $s->teacherSchedule->time_range }}</span>
                                        @endif
                                        @if ($s->teacher)
                                            · {{ $s->teacher->full_name }}
                                        @endif
                                    </span>
                                @endforeach
                            </div>

                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-500">Attended</span>
                                @foreach ($row['attended'] as $s)
                                    <span class="badge bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                        {{ $s->subject?->name ?? 'Homeroom' }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <span class="shrink-0 self-start rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 tabular-nums dark:bg-amber-500/10 dark:text-amber-300">
                            {{ $row['skipped']->count() }} cut
                        </span>
                    </div>
                @empty
                    <x-empty-state title="Nobody cut classes"
                                   description="Every advisory learner who scanned in today was present for all of their section's periods."
                                   icon="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                @endforelse
            </div>
        </x-card>
    </div>
</x-app-shell>
