<x-admin-layout title="Sections">
    <x-slot name="breadcrumbs">Admin / Sections</x-slot>

    <div x-data="resourceModal({
            base: '{{ url('admin/sections') }}',
            defaults: { school_year_id: '{{ $selectedYearId }}', grade_level_id: '', name: '', adviser_id: '', room: '', capacity: '' },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editModel ?? null),
            reopen: @js($errors->any() ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         })" class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex items-center gap-2">
                <label class="text-sm text-gray-500 dark:text-gray-400">School Year</label>
                <select name="school_year_id" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}" @selected($selectedYearId == $sy->id)>{{ $sy->name }}{{ $sy->is_active ? ' (active)' : '' }}</option>
                    @endforeach
                </select>
            </form>
            <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                New Section
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">Grade</th>
                            <th class="px-6 py-4">Section</th>
                            <th class="px-6 py-4">Adviser</th>
                            <th class="px-6 py-4">Room</th>
                            <th class="px-6 py-4 text-center">Learners</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($sections as $sec)
                            <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30 transition-colors">
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $sec->gradeLevel->name }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $sec->name }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $sec->adviser?->full_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $sec->room ?? '—' }}</td>
                                <td class="px-6 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400">{{ $sec->enrollments_count }}{{ $sec->capacity ? ' / '.$sec->capacity : '' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-action icon="subjects" :href="route('admin.assignments.index', $sec)" title="Manage subjects" color="gray" />
                                        <button type="button" @click='openEdit(@json($sec))' title="Edit section"
                                                class="inline-flex items-center justify-center p-2 rounded-lg text-brand-600 hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if ($sec->enrollments_count === 0)
                                            <x-confirm-delete :action="route('admin.sections.destroy', $sec)" title="Delete section?" message="Delete section {{ $sec->name }}? This cannot be undone." />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No sections for this school year</p>
                                <p class="mt-1 text-xs text-gray-500">Create one with the New Section button.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($sections->hasPages())<div>{{ $sections->links() }}</div>@endif

        {{-- Create / Edit modal --}}
        <x-form-modal create-title="New Section" edit-title="Edit Section"
                      create-subtitle="Add a class section to a school year"
                      edit-subtitle="Update this section"
                      submit-create="Create Section" submit-edit="Save Section">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School Year</label>
                    <select name="school_year_id" x-model="form.school_year_id" required
                            class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        @foreach ($schoolYears as $sy)
                            <option value="{{ $sy->id }}">{{ $sy->name }}{{ $sy->is_active ? ' (active)' : '' }}</option>
                        @endforeach
                    </select>
                    @error('school_year_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Grade Level</label>
                    <select name="grade_level_id" x-model="form.grade_level_id" required
                            class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="">— Select —</option>
                        @foreach ($gradeLevels as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                    @error('grade_level_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Section Name</label>
                <input type="text" name="name" x-model="form.name" required placeholder="e.g. Rizal, Newton, Sampaguita"
                       class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Class Adviser <span class="font-normal normal-case text-gray-400">(optional)</span></label>
                <select name="adviser_id" x-model="form.adviser_id"
                        class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    <option value="">— None —</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Room <span class="font-normal normal-case text-gray-400">(optional)</span></label>
                    <input type="text" name="room" x-model="form.room"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Capacity <span class="font-normal normal-case text-gray-400">(optional)</span></label>
                    <input type="number" name="capacity" x-model="form.capacity"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
            </div>
        </x-form-modal>
    </div>
</x-admin-layout>
