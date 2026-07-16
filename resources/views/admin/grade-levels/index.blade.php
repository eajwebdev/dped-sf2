<x-admin-layout title="Grade Levels">
    <x-slot name="breadcrumbs">Admin / Grade Levels</x-slot>

    <div x-data="resourceModal({
            base: '{{ url('admin/grade-levels') }}',
            defaults: { name: '', code: '', level_order: '', is_graduating: false },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editModel ?? null),
            reopen: @js($errors->any() ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         })" class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">Grade levels are shared across all school years. Order drives promotion.</p>
            <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                New Grade Level
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">Order</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Code</th>
                            <th class="px-6 py-4">Graduating</th>
                            <th class="px-6 py-4 text-center">Sections</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($gradeLevels as $g)
                            <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30 transition-colors">
                                <td class="px-6 py-4 text-gray-400 tabular-nums">{{ $g->level_order }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $g->name }}</td>
                                <td class="px-6 py-4"><code class="rounded-lg bg-gray-100 dark:bg-navy-900 px-2.5 py-1.5 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $g->code }}</code></td>
                                <td class="px-6 py-4">
                                    @if ($g->is_graduating)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 dark:bg-amber-500/15 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Yes</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400">{{ $g->sections_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click='openEdit(@json($g))' title="Edit grade level"
                                                class="inline-flex items-center justify-center p-2 rounded-lg text-brand-600 hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if ($g->sections_count === 0)
                                            <x-confirm-delete :action="route('admin.grade-levels.destroy', $g)" title="Delete grade level?" message="Delete {{ $g->name }}? This cannot be undone." />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No grade levels yet</p>
                                <p class="mt-1 text-xs text-gray-500">Create your first grade level to get started.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($gradeLevels->hasPages())<div>{{ $gradeLevels->links() }}</div>@endif

        {{-- Create / Edit modal --}}
        <x-form-modal create-title="New Grade Level" edit-title="Edit Grade Level"
                      create-subtitle="Grade levels are shared across all school years"
                      edit-subtitle="Update this grade level"
                      submit-create="Create Grade Level">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Name</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Grade 7"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Code</label>
                    <input type="text" name="code" x-model="form.code" required placeholder="e.g. G7"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('code')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Level Order</label>
                <input type="number" name="level_order" x-model="form.level_order" required min="1" max="20"
                       class="w-full sm:w-40 rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                <p class="mt-1 text-[11px] text-gray-400">Lower promotes to higher (e.g. 7 → 8).</p>
                @error('level_order')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <label class="inline-flex items-center gap-2.5 text-sm">
                <input type="checkbox" name="is_graduating" value="1" x-model="form.is_graduating" class="rounded border-gray-300 dark:border-white/15 text-brand-600 focus:ring-brand-500/20">
                <span>Graduating level <span class="text-gray-400">— learners here graduate instead of being promoted</span></span>
            </label>
        </x-form-modal>
    </div>
</x-admin-layout>
