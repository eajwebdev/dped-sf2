<x-admin-layout title="Subjects">
    <x-slot name="breadcrumbs">Admin / Subjects</x-slot>

    <div x-data="resourceModal({
            base: '{{ url('admin/subjects') }}',
            defaults: { name: '', code: '', grade_level_id: '', units: '', is_active: true },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editModel ?? null),
            reopen: @js($errors->any() ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         })" class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">Subjects offered, optionally tied to a grade level.</p>
            <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                New Subject
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Code</th>
                            <th class="px-6 py-4">Grade Level</th>
                            <th class="px-6 py-4">Units</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($subjects as $s)
                            <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $s->name }}</td>
                                <td class="px-6 py-4"><code class="rounded-lg bg-gray-100 dark:bg-navy-900 px-2.5 py-1.5 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $s->code }}</code></td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $s->gradeLevel?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $s->units ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if ($s->is_active)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-navy-700 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300"><span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click='openEdit(@json($s))' title="Edit subject"
                                                class="inline-flex items-center justify-center p-2 rounded-lg text-brand-600 hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <x-confirm-delete :action="route('admin.subjects.destroy', $s)" title="Delete subject?" message="Delete {{ $s->name }}? This cannot be undone." />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No subjects yet</p>
                                <p class="mt-1 text-xs text-gray-500">Create your first subject to get started.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($subjects->hasPages())<div>{{ $subjects->links() }}</div>@endif

        {{-- Create / Edit modal --}}
        <x-form-modal create-title="New Subject" edit-title="Edit Subject"
                      create-subtitle="Create a subject for your school"
                      edit-subtitle="Update this subject"
                      submit-create="Create Subject" submit-edit="Save Subject">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Name</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Mathematics"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Code</label>
                    <input type="text" name="code" x-model="form.code" required placeholder="e.g. MATH7"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('code')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Grade Level</label>
                    <select name="grade_level_id" x-model="form.grade_level_id"
                            class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="">— None (general) —</option>
                        @foreach ($gradeLevels as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Units <span class="font-normal normal-case text-gray-400">(optional)</span></label>
                    <input type="number" name="units" x-model="form.units"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
            </div>
            <label class="inline-flex items-center gap-2.5 text-sm">
                <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-300 dark:border-white/15 text-brand-600 focus:ring-brand-500/20">
                Active
            </label>
        </x-form-modal>
    </div>
</x-admin-layout>
