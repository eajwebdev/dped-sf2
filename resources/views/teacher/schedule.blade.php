@php
    use App\Models\TeacherSchedule;

    $dayStart = 6 * 60;   // grid begins 6:00 AM
    $dayEnd = 19 * 60;    // grid ends 7:00 PM
    $hourPx = 56;         // pixels per hour

    $palette = [
        'indigo'  => ['bg' => 'bg-brand-500/90',  'ring' => 'ring-brand-300 dark:ring-brand-700'],
        'emerald' => ['bg' => 'bg-emerald-500/90', 'ring' => 'ring-emerald-300 dark:ring-emerald-700'],
        'amber'   => ['bg' => 'bg-amber-500/90',   'ring' => 'ring-amber-300 dark:ring-amber-700'],
        'rose'    => ['bg' => 'bg-rose-500/90',    'ring' => 'ring-rose-300 dark:ring-rose-700'],
        'sky'     => ['bg' => 'bg-sky-500/90',     'ring' => 'ring-sky-300 dark:ring-sky-700'],
        'violet'  => ['bg' => 'bg-violet-500/90',  'ring' => 'ring-violet-300 dark:ring-violet-700'],
    ];

    $byDay = $schedules->groupBy('day_of_week');

    $entryJson = $schedules->map(fn ($s) => [
        'id' => $s->id,
        'section_id' => $s->section_id,
        'subject_id' => $s->subject_id,
        'day_of_week' => $s->day_of_week,
        'start_time' => substr($s->start_time, 0, 5),
        'end_time' => substr($s->end_time, 0, 5),
        'room' => $s->room,
        'color' => $s->color,
        'notes' => $s->notes,
    ])->values();
@endphp

<x-app-shell title="My Schedule" :wide="true">
    <div x-data="scheduleBoard({
            sections: @js($sections),
            entries: @js($entryJson),
            updateUrlBase: '{{ url('/schedule') }}',
         })" class="space-y-6">

        {{-- Header row --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Weekly teaching timetable
                    @if ($activeYear) · <span class="font-semibold text-brand-600 dark:text-brand-400">SY {{ $activeYear->name }}</span>@endif
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Classes here power the scan portal — the class happening now is auto-selected for QR check-in.</p>
            </div>
            <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                Add Class
            </button>
        </div>

        @if (! $activeYear)
            <div class="rounded-xl border border-amber-300 dark:border-amber-500/40 bg-amber-50 dark:bg-amber-500/10 px-5 py-4 text-sm text-amber-800 dark:text-amber-300">
                No active school year — ask an administrator to activate one before building your schedule.
            </div>
        @endif

        {{-- Today's classes — one tap to start a QR-attendance session --}}
        @php $todayEntries = ($byDay[now()->isoWeekday()] ?? collect())->sortBy('start_time'); @endphp
        @if ($todayEntries->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 p-5 shadow-sm">
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Today’s classes — start attendance</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($todayEntries as $entry)
                        <form method="POST" action="{{ route('class-sessions.start') }}">
                            @csrf
                            <input type="hidden" name="section_id" value="{{ $entry->section_id }}">
                            <input type="hidden" name="subject_id" value="{{ $entry->subject_id }}">
                            <input type="hidden" name="teacher_schedule_id" value="{{ $entry->id }}">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:shadow-lg hover:shadow-emerald-500/30 transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Start · {{ $entry->section->gradeLevel->name }} {{ $entry->section->name }}
                                <span class="text-xs font-normal text-white/80 tabular-nums">{{ $entry->time_range }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ======== Desktop: weekly grid ======== --}}
        <div class="hidden lg:block rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm overflow-hidden">
            <div class="grid" style="grid-template-columns: 64px repeat(6, 1fr);">
                {{-- Day headers --}}
                <div class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/60"></div>
                @foreach ([1,2,3,4,5,6] as $d)
                    <div class="border-b border-l border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/60 px-3 py-3 text-center">
                        <p class="text-xs font-bold uppercase tracking-wider {{ now()->isoWeekday() === $d ? 'text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400' }}">
                            {{ TeacherSchedule::DAYS[$d] }}
                        </p>
                        @if (now()->isoWeekday() === $d)
                            <span class="mt-1 inline-block h-1 w-8 rounded-full bg-brand-500"></span>
                        @endif
                    </div>
                @endforeach

                {{-- Time gutter --}}
                <div class="relative" style="height: {{ (($dayEnd - $dayStart) / 60) * $hourPx }}px">
                    @for ($h = intdiv($dayStart, 60); $h < intdiv($dayEnd, 60); $h++)
                        <div class="absolute right-2 -translate-y-1/2 text-[10px] font-medium text-gray-400 tabular-nums" style="top: {{ ($h * 60 - $dayStart) / 60 * $hourPx }}px">
                            {{ $h === 12 ? '12 PM' : ($h > 12 ? ($h - 12).' PM' : $h.' AM') }}
                        </div>
                    @endfor
                </div>

                {{-- Day columns --}}
                @foreach ([1,2,3,4,5,6] as $d)
                    <div class="relative border-l border-gray-200 dark:border-white/10 {{ now()->isoWeekday() === $d ? 'bg-brand-50/40 dark:bg-brand-500/5' : '' }}"
                         style="height: {{ (($dayEnd - $dayStart) / 60) * $hourPx }}px">
                        {{-- hour lines --}}
                        @for ($h = intdiv($dayStart, 60) + 1; $h < intdiv($dayEnd, 60); $h++)
                            <div class="absolute inset-x-0 border-t border-gray-100 dark:border-white/5" style="top: {{ ($h * 60 - $dayStart) / 60 * $hourPx }}px"></div>
                        @endfor

                        @foreach ($byDay->get($d, collect()) as $entry)
                            @php
                                [$sh, $sm] = explode(':', $entry->start_time);
                                [$eh, $em] = explode(':', $entry->end_time);
                                $top = max(0, ((int)$sh * 60 + (int)$sm - $dayStart) / 60 * $hourPx);
                                $height = max(28, (((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm)) / 60 * $hourPx - 3);
                                $tone = $palette[$entry->color] ?? $palette['indigo'];
                            @endphp
                            <button @click='openEdit(@json($entry->id))'
                                    class="absolute inset-x-1 z-10 overflow-hidden rounded-lg {{ $tone['bg'] }} px-2 py-1.5 text-left text-white shadow-sm ring-1 {{ $tone['ring'] }} hover:z-20 hover:shadow-lg transition-shadow"
                                    style="top: {{ $top }}px; height: {{ $height }}px">
                                <p class="truncate text-[11px] font-bold leading-tight">{{ $entry->section->gradeLevel->name }} — {{ $entry->section->name }}</p>
                                @if ($entry->subject)<p class="truncate text-[10px] text-white/85">{{ $entry->subject->name }}</p>@endif
                                <p class="truncate text-[10px] text-white/75 tabular-nums">{{ $entry->time_range }}@if($entry->room) · {{ $entry->room }}@endif</p>
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ======== Mobile: day-by-day list ======== --}}
        <div class="lg:hidden space-y-4">
            @foreach ([1,2,3,4,5,6] as $d)
                @php $dayEntries = $byDay->get($d, collect()); @endphp
                <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-white/10 {{ now()->isoWeekday() === $d ? 'bg-brand-50 dark:bg-brand-500/10' : 'bg-gray-50 dark:bg-navy-800/60' }}">
                        <h3 class="text-sm font-bold {{ now()->isoWeekday() === $d ? 'text-brand-700 dark:text-brand-300' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ TeacherSchedule::DAYS[$d] }}
                            @if (now()->isoWeekday() === $d)<span class="ml-2 rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold text-white">Today</span>@endif
                        </h3>
                        <span class="text-xs text-gray-400">{{ $dayEntries->count() }} {{ Str::plural('class', $dayEntries->count()) }}</span>
                    </div>
                    @if ($dayEntries->isEmpty())
                        <p class="px-5 py-4 text-xs text-gray-400">No classes.</p>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($dayEntries as $entry)
                                @php $tone = $palette[$entry->color] ?? $palette['indigo']; @endphp
                                <button @click='openEdit(@json($entry->id))' class="flex w-full items-center gap-3 px-5 py-3 text-left hover:bg-gray-50 dark:hover:bg-navy-700/30 transition-colors">
                                    <span class="h-10 w-1.5 rounded-full {{ $tone['bg'] }}"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $entry->section->gradeLevel->name }} — {{ $entry->section->name }}@if($entry->subject) · {{ $entry->subject->name }}@endif</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 tabular-nums">{{ $entry->time_range }}@if($entry->room) · Room {{ $entry->room }}@endif</span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ======== Add / Edit Modal ======== --}}
        <div x-show="modalOpen" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape.window="modalOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="modalOpen = false">
            <div x-show="modalOpen"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-navy-800 shadow-2xl">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-6 py-5 bg-gradient-to-r from-brand-50 to-blue-50 dark:from-navy-800 dark:to-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="editingId ? 'Edit Class' : 'Add Class'"></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Set the section, day, and time for this class</p>
                    </div>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-navy-700 rounded-lg transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="editingId"><input type="hidden" name="_method" value="PATCH"></template>

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3">
                            @foreach ($errors->all() as $error)
                                <p class="text-xs text-red-700 dark:text-red-300">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Section</label>
                        <select name="section_id" x-model="form.section_id" required
                                class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                            <option value="">Choose a section…</option>
                            <template x-for="s in sections" :key="s.id">
                                <option :value="s.id" x-text="s.label + (s.is_advisory ? ' (Advisory)' : '')" :selected="String(form.section_id) === String(s.id)"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-[11px] text-gray-400">Schedule any class in your school — advisory or not. Non-advisory classes you add here become available for their own SF2.</p>
                    </div>

                    <div x-show="subjectOptions.length > 0">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Subject <span class="font-normal normal-case text-gray-400">(optional)</span></label>
                        <select name="subject_id" x-model="form.subject_id"
                                class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                            <option value="">— Homeroom / Advisory —</option>
                            <template x-for="sub in subjectOptions" :key="sub.id">
                                <option :value="sub.id" x-text="sub.name" :selected="String(form.subject_id) === String(sub.id)"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Day</label>
                            <select name="day_of_week" x-model="form.day_of_week" required
                                    class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                @foreach (TeacherSchedule::DAYS as $num => $label)
                                    <option value="{{ $num }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Starts</label>
                            <input type="time" name="start_time" x-model="form.start_time" required
                                   class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Ends</label>
                            <input type="time" name="end_time" x-model="form.end_time" required
                                   class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Room <span class="font-normal normal-case text-gray-400">(optional)</span></label>
                            <input type="text" name="room" x-model="form.room" maxlength="50" placeholder="e.g. 204"
                                   class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Color</label>
                            <div class="flex items-center gap-2 pt-1.5">
                                @foreach (array_keys($palette) as $c)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="{{ $c }}" x-model="form.color" class="peer sr-only">
                                        <span class="block h-7 w-7 rounded-full {{ $palette[$c]['bg'] }} ring-2 ring-transparent peer-checked:ring-offset-2 peer-checked:ring-gray-900 dark:peer-checked:ring-white transition-all"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-gray-200 dark:border-white/10 pt-5">
                        <button type="button" x-show="editingId" @click="confirmDelete()"
                                class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Remove
                        </button>
                        <div class="ml-auto flex gap-3">
                            <button type="button" @click="modalOpen = false" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-navy-700 transition-colors">Cancel</button>
                            <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all" x-text="editingId ? 'Save Changes' : 'Add to Schedule'"></button>
                        </div>
                    </div>
                </form>

                {{-- Hidden delete form, submitted after SweetAlert confirms --}}
                <form x-ref="deleteForm" :action="formAction" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('scheduleBoard', (config) => ({
                sections: config.sections,
                entries: config.entries,
                modalOpen: false,
                editingId: null,
                form: { section_id: '', subject_id: '', day_of_week: '1', start_time: '07:00', end_time: '08:00', room: '', color: 'indigo', notes: '' },

                get formAction() {
                    return this.editingId ? `${config.updateUrlBase}/${this.editingId}` : config.updateUrlBase;
                },
                get subjectOptions() {
                    const s = this.sections.find((x) => String(x.id) === String(this.form.section_id));
                    return s ? s.subjects : [];
                },
                openCreate() {
                    this.editingId = null;
                    this.form = { section_id: '', subject_id: '', day_of_week: String(Math.min(new Date().getDay() || 7, 6)), start_time: '07:00', end_time: '08:00', room: '', color: 'indigo', notes: '' };
                    this.modalOpen = true;
                },
                openEdit(id) {
                    const e = this.entries.find((x) => x.id === id);
                    if (!e) return;
                    this.editingId = id;
                    this.form = {
                        section_id: String(e.section_id),
                        subject_id: e.subject_id ? String(e.subject_id) : '',
                        day_of_week: String(e.day_of_week),
                        start_time: e.start_time,
                        end_time: e.end_time,
                        room: e.room || '',
                        color: e.color || 'indigo',
                        notes: e.notes || '',
                    };
                    this.modalOpen = true;
                },
                confirmDelete() {
                    Swal.fire({
                        title: 'Remove this class?',
                        text: 'It will disappear from your weekly schedule and the scan portal.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, remove it',
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) this.$refs.deleteForm.submit();
                    });
                },
            }));
        });
    </script>

    @if ($errors->any())
        <script>
            {{-- Re-open the modal after a failed validation round-trip --}}
            document.addEventListener('alpine:initialized', () => {
                const root = document.querySelector('[x-data]');
                if (root && window.Alpine.$data(root)) window.Alpine.$data(root).modalOpen = true;
            });
        </script>
    @endif
</x-app-shell>
