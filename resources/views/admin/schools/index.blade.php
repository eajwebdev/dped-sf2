<x-admin-layout title="Schools">
    <x-slot name="breadcrumbs">Admin / Schools</x-slot>

    <div x-data="resourceModal({
            base: '{{ url('admin/schools') }}',
            defaults: {
                school_id: '',
                name: '',
                short_name: '',
                previous_name: '',
                mother_school_school_id: '',
                source_school_year: '',
                education_level: '',
                division: '',
                region: '',
                province: '',
                municipality: '',
                district: '',
                legislative_district: '',
                address: '',
                school_head: '',
                school_head_designation: '',
                telephone_number: '',
                fax_number: '',
                email: '',
                date_of_operation: '',
                sub_classification: '',
                curricular_class: '',
                school_type: '',
                class_organization: '',
                is_active: true
            },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editModel ?? null),
            reopen: @js($errors->any() ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         })" class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">Schools teachers can join at registration. Each carries its DepEd School ID and masterlist profile.</p>
            <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white transition-all hover:shadow-lg hover:shadow-brand-500/30">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                New School
            </button>
        </div>

        <form method="POST" action="{{ route('admin.schools.import-masterlist') }}" enctype="multipart/form-data"
              class="rounded-2xl border border-brand-100 bg-brand-50/70 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
            @csrf
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">Import DepEd school masterlist</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        Rebuilds and reads the <span class="font-semibold">FOR IMPORT</span> sheet. Leave file empty to import
                        <span class="font-mono">public/masterlist_of_schools_based_on_school_year_-_original.xls</span>.
                    </p>
                </div>
                <div class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_120px_auto] lg:w-[620px]">
                    <input type="file" name="masterlist" accept=".xls,.xlsx"
                           class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-white text-xs text-gray-500 file:mr-3 file:cursor-pointer file:border-0 file:bg-brand-600 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-white dark:border-white/15 dark:bg-navy-900 dark:text-gray-400">
                    <input type="text" name="sheet" value="FOR IMPORT"
                           class="rounded-lg border border-gray-300 px-3 py-2.5 text-xs dark:border-white/15 dark:bg-navy-900"
                           aria-label="Sheet name">
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-700">
                        Import
                    </button>
                </div>
            </div>
            @error('masterlist')<p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            @error('sheet')<p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-navy-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-navy-800/50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">School ID</th>
                            <th class="px-6 py-4">Logo</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Level</th>
                            <th class="px-6 py-4">Location</th>
                            <th class="px-6 py-4">School Head / Contact</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Teachers</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($schools as $school)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-navy-700/30">
                                <td class="px-6 py-4">
                                    <code class="rounded-lg bg-gray-100 px-2.5 py-1.5 font-mono text-xs text-gray-700 dark:bg-navy-900 dark:text-gray-300">{{ $school->school_id }}</code>
                                    @if ($school->mother_school_school_id && $school->mother_school_school_id !== $school->school_id)
                                        <p class="mt-1 text-[11px] text-gray-500">Mother: {{ $school->mother_school_school_id }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($school->logo_path)
                                        <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }} logo"
                                             class="h-10 w-10 rounded-xl border border-gray-200 bg-white object-contain p-0.5 dark:border-white/10">
                                    @else
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-white/5">
                                            <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m4-4h.01M7 13h.01M7 9h.01M11 17h.01M11 13h.01M11 9h.01M15 17h.01M15 13h.01M15 9h.01"/></svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $school->name }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ collect([$school->short_name, $school->previous_name ? 'Prev: '.$school->previous_name : null])->filter()->join(' · ') ?: '—' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($school->educationLevelLabel())
                                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ strtoupper(str_replace('_', '+', $school->education_level)) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-500">{{ collect([$school->curricular_class, $school->class_organization])->filter()->join(' · ') }}</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    <p>{{ collect([$school->municipality, $school->province])->filter()->join(', ') ?: '—' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ collect([$school->district, $school->division, $school->region])->filter()->join(' · ') ?: '—' }}</p>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    <p>{{ $school->school_head ?: '—' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ collect([$school->school_head_designation, $school->telephone_number, $school->email])->filter()->join(' · ') ?: '—' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($school->is_active)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-500 dark:bg-navy-700 dark:text-gray-400"><span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Inactive</span>
                                    @endif
                                    @if ($school->source_school_year)
                                        <p class="mt-1 text-[11px] text-gray-500">SY {{ $school->source_school_year }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400">{{ $school->users_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click='openEdit(@json($school))' title="Edit school"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-brand-600 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if ($school->users_count === 0)
                                            <x-confirm-delete :action="route('admin.schools.destroy', $school)" title="Delete school?" message="Delete {{ $school->name }}? This cannot be undone." />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No schools yet</p>
                                <p class="mt-1 text-xs text-gray-500">Add a school or import the masterlist so teachers can register into it.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($schools->hasPages())<div>{{ $schools->links() }}</div>@endif

        <x-form-modal create-title="New School" edit-title="Edit School"
                      create-subtitle="Teachers select this school when they register"
                      edit-subtitle="Update this school"
                      submit-create="Add School">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School ID</label>
                    <input type="text" name="school_id" x-model="form.school_id" required placeholder="e.g. 123456"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-white/15 dark:bg-navy-900">
                    @error('school_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School Name</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Dela Paz Central School"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-white/15 dark:bg-navy-900">
                    @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Short Name</label>
                    <input type="text" name="short_name" x-model="form.short_name"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-white/15 dark:bg-navy-900">
                    @error('short_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Previous Name</label>
                    <input type="text" name="previous_name" x-model="form.previous_name"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-white/15 dark:bg-navy-900">
                    @error('previous_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Education Level</label>
                    <select name="education_level" x-model="form.education_level" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-white/15 dark:bg-navy-900">
                        <option value="">— Select level —</option>
                        @foreach (\App\Models\School::LEVELS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('education_level')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Mother School ID</label>
                    <input type="text" name="mother_school_school_id" x-model="form.mother_school_school_id"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-white/15 dark:bg-navy-900">
                    @error('mother_school_school_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Region</label>
                    <input type="text" name="region" x-model="form.region" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('region')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Division</label>
                    <input type="text" name="division" x-model="form.division" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('division')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Province</label>
                    <input type="text" name="province" x-model="form.province" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('province')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Municipality</label>
                    <input type="text" name="municipality" x-model="form.municipality" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('municipality')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">District</label>
                    <input type="text" name="district" x-model="form.district" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('district')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Legislative District</label>
                    <input type="text" name="legislative_district" x-model="form.legislative_district" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('legislative_district')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Address</label>
                <input type="text" name="address" x-model="form.address" placeholder="Street, barangay, city"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-white/15 dark:bg-navy-900">
                @error('address')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School Head</label>
                    <input type="text" name="school_head" x-model="form.school_head" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('school_head')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Designation</label>
                    <input type="text" name="school_head_designation" x-model="form.school_head_designation" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('school_head_designation')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Telephone Number</label>
                    <input type="text" name="telephone_number" x-model="form.telephone_number" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('telephone_number')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Fax Number</label>
                    <input type="text" name="fax_number" x-model="form.fax_number" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('fax_number')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">E-mail</label>
                    <input type="text" name="email" x-model="form.email" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('email')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Date of Operation</label>
                    <input type="date" name="date_of_operation"
                           :value="form.date_of_operation ? String(form.date_of_operation).slice(0, 10) : ''"
                           @input="form.date_of_operation = $event.target.value"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('date_of_operation')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Source School Year</label>
                    <input type="text" name="source_school_year" x-model="form.source_school_year" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('source_school_year')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Sub-Classification</label>
                    <input type="text" name="sub_classification" x-model="form.sub_classification" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('sub_classification')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Curricular Class</label>
                    <input type="text" name="curricular_class" x-model="form.curricular_class" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('curricular_class')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School Type</label>
                    <input type="text" name="school_type" x-model="form.school_type" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('school_type')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Class Organization</label>
                    <input type="text" name="class_organization" x-model="form.class_organization" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-white/15 dark:bg-navy-900">
                    @error('class_organization')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div x-data="{ logoPreview: null }" x-effect="if (open) logoPreview = null">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">School Logo</label>
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-gray-300 bg-gray-50 dark:border-white/15 dark:bg-navy-900">
                        <template x-if="logoPreview || form.logo_path">
                            <img :src="logoPreview || ('/' + form.logo_path)" alt="Logo preview" class="h-full w-full object-contain p-1">
                        </template>
                        <svg x-show="!logoPreview && !form.logo_path" class="h-6 w-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm10.5-11.25h.008v.008h-.008V9.75Z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                               @change="const f = $event.target.files[0]; if (f) logoPreview = URL.createObjectURL(f)"
                               class="block w-full cursor-pointer text-xs text-gray-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-brand-600 hover:file:bg-brand-100 dark:text-gray-400 dark:file:bg-brand-500/15 dark:file:text-brand-300">
                        <p class="mt-1 text-xs text-gray-400">PNG, JPG, or WebP up to 2 MB. Shown on printed forms later.</p>
                    </div>
                </div>
                @error('logo')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <label class="inline-flex items-center gap-2.5 text-sm">
                <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500/20 dark:border-white/15">
                <span>Active <span class="text-gray-400">— teachers can register into this school</span></span>
            </label>
        </x-form-modal>
    </div>
</x-admin-layout>
