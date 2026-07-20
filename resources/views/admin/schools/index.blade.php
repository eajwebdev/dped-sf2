<x-admin-layout title="Schools">
    <x-slot name="breadcrumbs">Admin / Schools</x-slot>

    <div x-data="resourceModal({
            base: '{{ url('admin/schools') }}',
            defaults: { school_id: '', name: '', education_level: '', division: '', region: '', address: '', is_active: true },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editModel ?? null),
            reopen: @js($errors->any() ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         })" class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">Schools teachers can join at registration. Each carries its DepEd School ID.</p>
            <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                New School
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">School ID</th>
                            <th class="px-6 py-4">Logo</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Level</th>
                            <th class="px-6 py-4">Division / Region</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Teachers</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($schools as $school)
                            <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30 transition-colors">
                                <td class="px-6 py-4"><code class="rounded-lg bg-gray-100 dark:bg-navy-900 px-2.5 py-1.5 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $school->school_id }}</code></td>
                                <td class="px-6 py-4">
                                    @if ($school->logo_path)
                                        <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }} logo"
                                             class="h-10 w-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white object-contain p-0.5">
                                    @else
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-white/5">
                                            <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m4-4h.01M7 13h.01M7 9h.01M11 17h.01M11 13h.01M11 9h.01M15 17h.01M15 13h.01M15 9h.01"/></svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $school->name }}</td>
                                <td class="px-6 py-4">
                                    @if ($school->educationLevelLabel())
                                        <span class="inline-flex items-center rounded-full bg-brand-50 dark:bg-brand-500/10 px-3 py-1.5 text-xs font-semibold text-brand-700 dark:text-brand-300">{{ strtoupper(str_replace('_', '+', $school->education_level)) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ collect([$school->division, $school->region])->filter()->join(' · ') ?: '—' }}</td>
                                <td class="px-6 py-4">
                                    @if ($school->is_active)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-navy-700 px-3 py-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400"><span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400">{{ $school->users_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click='openEdit(@json($school))' title="Edit school"
                                                class="inline-flex items-center justify-center p-2 rounded-lg text-brand-600 hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if ($school->users_count === 0)
                                            <x-confirm-delete :action="route('admin.schools.destroy', $school)" title="Delete school?" message="Delete {{ $school->name }}? This cannot be undone." />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No schools yet</p>
                                <p class="mt-1 text-xs text-gray-500">Add a school so teachers can register into it.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($schools->hasPages())<div>{{ $schools->links() }}</div>@endif

        {{-- Create / Edit modal --}}
        <x-form-modal create-title="New School" edit-title="Edit School"
                      create-subtitle="Teachers select this school when they register"
                      edit-subtitle="Update this school"
                      submit-create="Add School">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School ID</label>
                    <input type="text" name="school_id" x-model="form.school_id" required placeholder="e.g. 123456"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('school_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School Name</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Dela Paz Central School"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Education Level</label>
                    <select name="education_level" x-model="form.education_level" required
                            class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="">— Select level —</option>
                        @foreach (\App\Models\School::LEVELS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Elementary is its own school; JHS and SHS may share one campus.</p>
                    @error('education_level')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Division</label>
                    <input type="text" name="division" x-model="form.division" placeholder="e.g. Antipolo City"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('division')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Region</label>
                    <input type="text" name="region" x-model="form.region" placeholder="e.g. Region IV-A"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('region')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Address</label>
                <input type="text" name="address" x-model="form.address" placeholder="Street, barangay, city"
                       class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                @error('address')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            {{-- School logo upload (used later on SF2 / printed forms) --}}
            <div x-data="{ logoPreview: null }" x-effect="if (open) logoPreview = null">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School Logo</label>
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-gray-300 dark:border-white/15 bg-gray-50 dark:bg-navy-900">
                        <template x-if="logoPreview || form.logo_path">
                            <img :src="logoPreview || ('{{ Illuminate\Support\Facades\Storage::disk('public')->url('') }}' + form.logo_path)"
                                 alt="Logo preview" class="h-full w-full object-contain p-1">
                        </template>
                        <svg x-show="!logoPreview && !form.logo_path" class="h-6 w-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm10.5-11.25h.008v.008h-.008V9.75Z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                               @change="const f = $event.target.files[0]; if (f) logoPreview = URL.createObjectURL(f)"
                               class="block w-full cursor-pointer text-xs text-gray-500 dark:text-gray-400
                                      file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2.5
                                      file:text-xs file:font-bold file:text-brand-600 hover:file:bg-brand-100
                                      dark:file:bg-brand-500/15 dark:file:text-brand-300">
                        <p class="mt-1 text-xs text-gray-400">PNG, JPG, or WebP up to 2 MB. Shown on printed forms later.</p>
                    </div>
                </div>
                @error('logo')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <label class="inline-flex items-center gap-2.5 text-sm">
                <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-300 dark:border-white/15 text-brand-600 focus:ring-brand-500/20">
                <span>Active <span class="text-gray-400">— teachers can register into this school</span></span>
            </label>
        </x-form-modal>
    </div>
</x-admin-layout>
