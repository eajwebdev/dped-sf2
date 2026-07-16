@php
    $keymap = [];
    foreach ($statuses as $slug => $meta) { $keymap[$meta['key']] = $slug; }
    $rows = $roster->map(fn ($e) => [
        'enrollment_id' => $e->id,
        'name' => $e->student->full_name,
        'lrn' => $e->student->lrn,
        'gender' => $e->student->gender,
        'status' => optional($existing->get($e->id))->status ?? '',
        'remarks' => optional($existing->get($e->id))->remarks ?? '',
    ])->values();
@endphp

<x-app-shell :title="null" wide>
    {{-- Tailwind safelist: status button colors are applied via Alpine :class from PHP config --}}
    <span class="hidden bg-emerald-500 bg-red-500 bg-amber-500 bg-blue-500 bg-violet-500"></span>

    <div x-data="attendanceGrid(@js([
        'rows' => $rows,
        'statuses' => $statuses,
        'keymap' => $keymap,
        'editable' => $editable,
        'saveUrl' => route('attendance.save', $section),
        'date' => $date->toDateString(),
        'autosaveMs' => ($settings->autosave_seconds ?? 15) * 1000,
        'lastSaved' => null,
        'summary' => $summary,
    ]))" @keydown.window="onKey($event)">

        {{-- Header --}}
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('attendance.index') }}" class="text-xs text-gray-400 hover:text-gray-600">&larr; All classes</a>
                <h1 class="text-lg font-semibold">{{ $section->gradeLevel->name }} — {{ $section->name }}</h1>
                <p class="text-xs text-gray-400">SY {{ $section->schoolYear->name }} · Adviser: {{ $section->adviser?->full_name ?? '—' }}</p>
            </div>
            <div class="flex items-end gap-2">
                <a href="{{ route('attendance.scan', ['section' => $section, 'date' => $date->toDateString()]) }}"
                   class="rounded-lg border border-gray-300 dark:border-white/15 px-3 py-2 text-sm font-medium hover:bg-gray-50 dark:hover:bg-navy-700/50">📷 Scan QR</a>
                <form method="GET" action="{{ route('attendance.sheet', $section) }}">
                    <label class="block text-xs text-gray-400">Date</label>
                    <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                           class="mt-1 rounded-lg border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm focus:border-brand-500 focus:ring-brand-500">
                </form>
            </div>
        </div>

        {{-- Lock / non-class-day banner --}}
        @unless ($editable)
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                <span>
                    @switch($lockReason)
                        @case('future') This date is in the future — attendance can't be recorded yet. @break
                        @case('holiday') {{ $date->format('M d, Y') }} is not a class day (holiday/weekend/suspension). @break
                        @case('locked') This date is locked (older than the edit window). @break
                        @default Attendance for this date is read-only.
                    @endswitch
                </span>
                @if ($lockReason === 'locked' && auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('attendance.unlock', $section) }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                        <button class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-700">Unlock for editing</button>
                    </form>
                @endif
            </div>
        @endunless

        {{-- Toolbar: summary + save state + shortcuts --}}
        <div class="mb-3 flex flex-wrap items-center gap-3 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 px-4 py-2.5 text-sm">
            <div class="flex items-center gap-3">
                <span class="font-medium text-emerald-600" x-text="summary.present + ' P'"></span>
                <span class="font-medium text-red-600" x-text="summary.absent + ' A'"></span>
                <span class="font-medium text-amber-500" x-text="summary.late + ' L'"></span>
                <span class="font-medium text-blue-600" x-text="summary.excused + ' E'"></span>
                <span class="text-gray-400" x-text="summary.unmarked + ' unmarked'"></span>
            </div>
            @if ($editable)
                <div class="ml-auto flex items-center gap-2">
                    <span x-show="saving" class="text-xs text-gray-400">Saving…</span>
                    <span x-show="!saving && lastSaved" x-cloak class="text-xs text-emerald-600" x-text="'Saved ' + lastSaved"></span>
                    <span x-show="saveError" x-cloak class="text-xs text-red-600" x-text="saveError"></span>
                    <button type="button" @click="markAllPresent()" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300">Mark all present</button>
                    <button type="button" @click="save()" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-700">Save now</button>
                </div>
            @endif
        </div>

        <p class="mb-2 text-[11px] text-gray-400">Shortcuts: click a row, then press <b>P</b> Present · <b>A</b> Absent · <b>L</b> Late · <b>E</b> Excused · <b>H</b> Half-day · <b>↑/↓/Enter</b> to move.</p>

        {{-- Grid --}}
        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800" style="max-height: 70vh">
            <table class="min-w-full text-sm">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-navy-900">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-400">
                        <th class="sticky left-0 z-20 bg-gray-50 dark:bg-navy-900 px-3 py-2 w-10">#</th>
                        <th class="sticky left-10 z-20 bg-gray-50 dark:bg-navy-900 px-3 py-2 min-w-[16rem]">Learner</th>
                        <th class="px-3 py-2 text-center">Status</th>
                        <th class="px-3 py-2 min-w-[12rem]">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <template x-for="(row, i) in rows" :key="row.enrollment_id">
                        <tr :data-row="i" @click="cursor = i"
                            :class="cursor === i ? 'bg-brand-50/60 dark:bg-brand-500/10' : 'hover:bg-gray-50 dark:hover:bg-navy-700/30'">
                            <td class="sticky left-0 z-10 px-3 py-2 text-gray-400"
                                :class="cursor === i ? 'bg-brand-50 dark:bg-navy-800' : 'bg-white dark:bg-navy-800'" x-text="i + 1"></td>
                            <td class="sticky left-10 z-10 px-3 py-2"
                                :class="cursor === i ? 'bg-brand-50 dark:bg-navy-800' : 'bg-white dark:bg-navy-800'">
                                <span class="font-medium" x-text="row.name"></span>
                                <span class="block font-mono text-[11px] text-gray-400" x-text="row.lrn"></span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center justify-center gap-1">
                                    <template x-for="(meta, slug) in statuses" :key="slug">
                                        <button type="button" @click="setStatus(i, slug)" :disabled="!editable"
                                                class="h-8 w-8 rounded-md text-xs font-bold text-white transition disabled:cursor-not-allowed"
                                                :class="row.status === slug ? meta.class + ' ring-2 ring-offset-1 ring-gray-400 dark:ring-offset-gray-800' : 'bg-gray-200 text-gray-500 dark:bg-navy-700 hover:opacity-80'"
                                                x-text="meta.key" :title="meta.label"></button>
                                    </template>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" x-model="row.remarks" @input="markDirty(row.enrollment_id)" :disabled="!editable"
                                       placeholder="—" class="w-full rounded-md border-gray-200 dark:border-white/15 dark:bg-navy-900 text-xs py-1 focus:border-brand-500 focus:ring-brand-500 disabled:bg-gray-100 dark:disabled:bg-gray-800">
                            </td>
                        </tr>
                    </template>
                    <tr x-show="rows.length === 0"><td colspan="4" class="px-3 py-10 text-center text-gray-400">No active learners enrolled in this section.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('attendanceGrid', (config) => ({
                rows: config.rows,
                statuses: config.statuses,
                keymap: config.keymap,
                editable: config.editable,
                saveUrl: config.saveUrl,
                date: config.date,
                autosaveMs: config.autosaveMs,
                cursor: 0,
                dirty: {},
                saving: false,
                lastSaved: config.lastSaved,
                saveError: null,
                summary: config.summary,
                timer: null,

                init() {
                    window.addEventListener('beforeunload', (e) => {
                        if (Object.keys(this.dirty).length) { e.preventDefault(); e.returnValue = ''; }
                    });
                },
                setStatus(i, status) {
                    if (!this.editable) return;
                    this.rows[i].status = status;
                    this.markDirty(this.rows[i].enrollment_id);
                    this.recount();
                },
                markDirty(id) { this.dirty[id] = true; this.saveError = null; this.scheduleSave(); },
                scheduleSave() { clearTimeout(this.timer); this.timer = setTimeout(() => this.save(), this.autosaveMs); },
                markAllPresent() {
                    if (!this.editable) return;
                    this.rows.forEach((r) => { if (!r.status) { r.status = 'present'; this.dirty[r.enrollment_id] = true; } });
                    this.recount(); this.save();
                },
                onKey(e) {
                    if (!this.editable) return;
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
                    const k = e.key.toUpperCase();
                    if (k === 'ARROWDOWN' || k === 'ENTER') { e.preventDefault(); this.move(1); return; }
                    if (k === 'ARROWUP') { e.preventDefault(); this.move(-1); return; }
                    if (this.keymap[k]) { e.preventDefault(); this.setStatus(this.cursor, this.keymap[k]); this.move(1); }
                },
                move(d) {
                    this.cursor = Math.max(0, Math.min(this.rows.length - 1, this.cursor + d));
                    this.$nextTick(() => {
                        const el = this.$root.querySelector('[data-row="' + this.cursor + '"]');
                        if (el) el.scrollIntoView({ block: 'nearest' });
                    });
                },
                recount() {
                    const c = { present: 0, absent: 0, late: 0, excused: 0, half_day: 0, unmarked: 0 };
                    this.rows.forEach((r) => { if (r.status) { c[r.status] = (c[r.status] || 0) + 1; } else { c.unmarked++; } });
                    c.total = this.rows.length; this.summary = c;
                },
                async save() {
                    const changed = this.rows.filter((r) => this.dirty[r.enrollment_id])
                        .map((r) => ({ enrollment_id: r.enrollment_id, status: r.status, remarks: r.remarks }));
                    if (!changed.length || this.saving) return;
                    this.saving = true; this.saveError = null;
                    try {
                        const res = await window.axios.post(this.saveUrl, { date: this.date, marks: changed });
                        this.dirty = {}; this.lastSaved = res.data.savedAt; this.summary = res.data.summary;
                    } catch (err) {
                        this.saveError = err.response?.data?.errors?.[0] || 'Save failed — retrying…';
                        this.scheduleSave();
                    } finally { this.saving = false; }
                },
            }));
        });
    </script>
</x-app-shell>
