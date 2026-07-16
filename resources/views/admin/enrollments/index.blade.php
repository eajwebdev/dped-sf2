<x-admin-layout title="Enrollment">
    <x-slot name="breadcrumbs">Admin / Enrollment</x-slot>

    {{-- Year + section pickers --}}
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-400">School Year</label>
            <select name="school_year_id" onchange="this.form.submit()" class="mt-1 rounded-lg border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm focus:border-brand-500 focus:ring-brand-500">
                @foreach ($schoolYears as $sy)
                    <option value="{{ $sy->id }}" @selected($selectedYearId == $sy->id)>{{ $sy->name }}{{ $sy->is_active ? ' (active)' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400">Section</label>
            <select name="section_id" onchange="this.form.submit()" class="mt-1 rounded-lg border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm focus:border-brand-500 focus:ring-brand-500">
                @forelse ($sections as $sec)
                    <option value="{{ $sec->id }}" @selected($section && $section->id === $sec->id)>{{ $sec->gradeLevel->name }} — {{ $sec->name }}</option>
                @empty
                    <option value="">No sections for this year</option>
                @endforelse
            </select>
        </div>
    </form>

    @if (! $section)
        <x-card><p class="py-6 text-center text-gray-400">Create a section for this school year first.</p></x-card>
    @else
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            {{-- Current roster --}}
            <x-card title="Roster — {{ $section->gradeLevel->name }} {{ $section->name }} ({{ $roster->count() }})">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                        <thead><tr class="text-left text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-2 py-2">Learner</th><th class="px-2 py-2">Status</th><th class="px-2 py-2 text-right">Actions</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse ($roster as $e)
                                <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30">
                                    <td class="px-2 py-2">
                                        <a href="{{ route('admin.students.show', $e->student) }}" class="font-medium hover:text-brand-600">{{ $e->student->full_name }}</a>
                                        <span class="block font-mono text-[11px] text-gray-400">{{ $e->student->lrn }}</span>
                                    </td>
                                    <td class="px-2 py-2"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] capitalize dark:bg-navy-700">{{ str_replace('_',' ',$e->status) }}</span></td>
                                    <td class="px-2 py-2">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Transfer within the same year --}}
                                            <form method="POST" action="{{ route('admin.enrollments.transfer', $e) }}">
                                                @csrf @method('PATCH')
                                                <select name="section_id" onchange="this.form.submit()" class="rounded-md border-gray-200 dark:border-white/15 dark:bg-navy-900 text-[11px] py-1 focus:border-brand-500 focus:ring-brand-500">
                                                    <option value="">Transfer…</option>
                                                    @foreach ($sections->where('id','!=',$section->id) as $sec)
                                                        <option value="{{ $sec->id }}">{{ $sec->gradeLevel->name }} {{ $sec->name }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                            @if ($e->status === 'enrolled')
                                                <form method="POST" action="{{ route('admin.enrollments.status', $e) }}" class="js-confirm inline"
                                                      data-title="Drop this learner?" data-message="Mark {{ $e->student->first_name }} as dropped for this year?"
                                                      data-confirm="Yes, drop" data-icon="question">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="dropped">
                                                    <x-action icon="drop" title="Drop learner" color="amber" />
                                                </form>
                                            @endif
                                            <x-confirm-delete :action="route('admin.enrollments.destroy', $e)" title="Remove enrollment?" message="Remove this enrollment? Only allowed if no attendance exists." />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-2 py-8 text-center text-gray-400">No learners enrolled yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            {{-- Add learners --}}
            <x-card title="Add Learners">
                <form method="GET" class="mb-3 flex gap-2">
                    <input type="hidden" name="school_year_id" value="{{ $selectedYearId }}">
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search unassigned learners…" class="w-full rounded-lg border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <button class="rounded-lg bg-gray-100 dark:bg-navy-700 px-3 text-sm">Find</button>
                </form>

                <form method="POST" action="{{ route('admin.enrollments.store') }}" x-data="{ any: false }">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                    <div class="max-h-80 overflow-y-auto rounded-lg border border-gray-200 dark:border-white/10">
                        @forelse ($available as $stud)
                            <label class="flex items-center gap-3 border-b border-gray-100 dark:border-white/5 px-3 py-2 last:border-0 hover:bg-gray-50 dark:hover:bg-navy-700/30 cursor-pointer">
                                <input type="checkbox" name="student_ids[]" value="{{ $stud->id }}" @change="any = $el.closest('form').querySelectorAll('input[name=\'student_ids[]\']:checked').length > 0"
                                       class="rounded border-gray-300 dark:border-white/15 dark:bg-navy-900 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm">{{ $stud->full_name }} <span class="font-mono text-[11px] text-gray-400">{{ $stud->lrn }}</span></span>
                            </label>
                        @empty
                            <p class="px-3 py-8 text-center text-sm text-gray-400">{{ $search ? 'No matching unassigned learners.' : 'All active learners are already enrolled this year.' }}</p>
                        @endforelse
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs text-gray-500">
                            <input type="checkbox" name="is_late_enrollment" value="1" class="rounded border-gray-300 dark:border-white/15 dark:bg-navy-900 text-brand-600 focus:ring-brand-500">
                            Mark as late enrollment (beyond June cut-off)
                        </label>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-50" :disabled="!any">Enroll selected</button>
                    </div>
                </form>
            </x-card>
        </div>
    @endif
</x-admin-layout>
