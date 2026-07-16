@php
    $syRow = fn ($sy) => [
        'id' => $sy->id,
        'name' => $sy->name,
        'start_date' => $sy->start_date?->toDateString(),
        'end_date' => $sy->end_date?->toDateString(),
    ];
    $editRow = isset($editModel) ? $syRow($editModel) : null;
@endphp

<x-admin-layout title="School Years">
    <x-slot name="breadcrumbs">Admin / School Years</x-slot>

    <div x-data="resourceModal({
            base: '{{ url('admin/school-years') }}',
            defaults: { name: '', start_date: '', end_date: '' },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editRow),
            reopen: @js($errors->any() ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         })">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage academic years. Only one can be active at a time.</p>
        <button @click="openCreate()"
           class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
            New School Year
        </button>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-400">
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Period</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2 text-center">Sections</th>
                        <th class="px-3 py-2 text-center">Enrollments</th>
                        <th class="px-3 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($schoolYears as $sy)
                        <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30">
                            <td class="px-3 py-3 font-medium">
                                {{ $sy->name }}
                                @if ($sy->is_active)
                                    <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Active</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-gray-500 dark:text-gray-400">{{ $sy->start_date->format('M d, Y') }} – {{ $sy->end_date->format('M d, Y') }}</td>
                            <td class="px-3 py-3">
                                @php $badge = ['open' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300','closed' => 'bg-gray-100 text-gray-600 dark:bg-navy-700 dark:text-gray-300','archived' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'][$sy->status]; @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium capitalize {{ $badge }}">{{ $sy->status }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">{{ $sy->sections_count }}</td>
                            <td class="px-3 py-3 text-center">{{ $sy->enrollments_count }}</td>
                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($sy->is_active)
                                        <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
                                            <button type="button" @click="open = !open"
                                                    class="rounded-lg px-2 py-1 text-xs font-semibold text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-500/10"
                                                    title="Set as active year">
                                                Activate
                                            </button>
                                            <div x-show="open" x-cloak x-transition
                                                 class="absolute right-0 z-20 mt-1 w-64 rounded-xl border border-slate-200 bg-white p-3 text-left shadow-lift dark:border-white/10 dark:bg-navy-800">
                                                <form method="POST" action="{{ route('admin.school-years.activate', $sy) }}">@csrf
                                                    <input type="hidden" name="scope" value="all">
                                                    <button type="submit" class="btn-primary btn-sm w-full">Activate for all schools</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.school-years.activate', $sy) }}" class="mt-2 space-y-2">@csrf
                                                    <input type="hidden" name="scope" value="school">
                                                    <x-school-select name="school_id" :schools="$schools" :required="true" placeholder="One school only — search…" />
                                                    <button type="submit" class="btn-outline btn-sm w-full">Activate for that school</button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('admin.school-years.close', $sy) }}" class="inline">@csrf
                                            <x-action icon="close" title="Close year" color="gray" />
                                        </form>
                                    @endunless
                                    @if ($sy->status !== 'archived')
                                        <form method="POST" action="{{ route('admin.school-years.archive', $sy) }}" class="inline" onsubmit="return confirm('Archive {{ $sy->name }}? It will become read-only.');">@csrf
                                            <x-action icon="archive" title="Archive year" color="amber" />
                                        </form>
                                    @endif
                                    <button type="button" @click='openEdit(@json($syRow($sy)))' title="Edit year"
                                            class="inline-flex items-center justify-center p-2 rounded-lg text-brand-600 hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10 transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if ($sy->enrollments_count === 0)
                                        <x-confirm-delete :action="route('admin.school-years.destroy', $sy)" title="Delete school year?" message="Delete {{ $sy->name }}? This cannot be undone." />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-10 text-center text-gray-400">No school years yet. Create your first one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $schoolYears->links() }}</div>
    </x-card>

    {{-- Create / Edit modal --}}
    <x-form-modal create-title="New School Year" edit-title="Edit School Year"
                  create-subtitle="Saving generates the day-by-day class calendar"
                  edit-subtitle="Changing dates regenerates the class calendar"
                  submit-create="Create School Year" submit-edit="Save School Year" max-width="max-w-xl">
        <div>
            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School Year Name</label>
            <input type="text" name="name" x-model="form.name" required placeholder="e.g. 2025-2026"
                   class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
            @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Start Date</label>
                <input type="date" name="start_date" x-model="form.start_date" required
                       class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                @error('start_date')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">End Date</label>
                <input type="date" name="end_date" x-model="form.end_date" required
                       class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                @error('end_date')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>
        <p class="text-xs text-gray-400">Saving generates the day-by-day class calendar (weekends and holidays excluded automatically).</p>
    </x-form-modal>
    </div>
</x-admin-layout>
