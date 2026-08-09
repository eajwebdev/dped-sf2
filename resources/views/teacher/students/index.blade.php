<x-app-shell title="My Students" :wide="true">
    @php
        $importHasErrors = $errors->has('section_id') || $errors->has('file');
        $sectionHasErrors = $errors->has('grade_level_id') || $errors->has('name') || $errors->has('room') || $errors->has('capacity');
    @endphp

    <div x-data="{ ...resourceModal({
            base: '{{ url('students') }}',
            defaults: { lrn: '', first_name: '', middle_name: '', last_name: '', suffix: '', gender: 'Male', birthdate: '', address: '', guardian_name: '', guardian_contact: '', status: 'active', birth_place: '', mother_tongue: '', ethnic_group: '', religion: '', address_street: '', address_barangay: '', address_municipality: '', address_province: '', father_name: '', mother_name: '', guardian_relationship: '' },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editModel ?? null),
            reopen: @js($errors->any() && ! $importHasErrors && ! $sectionHasErrors ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         }), importOpen: @js($importHasErrors), sectionOpen: @js($sectionHasErrors), importSectionId: @js(old('section_id', $selectedSectionId ? (string) $selectedSectionId : '')) }" class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex w-full flex-col gap-2 sm:max-w-2xl sm:flex-row">
                <div class="relative flex-1">
                <input type="search" name="q" value="{{ $search }}" placeholder="Search name or LRN…"
                       class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm pl-4 pr-10 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <select name="section_id" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    <option value="">All advisory students</option>
                    @foreach ($advisorySections as $section)
                        <option value="{{ $section->id }}" @selected($selectedSectionId === $section->id)>
                            {{ $section->gradeLevel?->name }} - {{ $section->name }}
                        </option>
                    @endforeach
                </select>
                <label class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-white/15 dark:text-gray-200">
                    <input type="checkbox" name="needs_info" value="1" @checked($needsInfo) onchange="this.form.submit()" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Needs Info
                </label>
            </form>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <button @click="sectionOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-white/15 px-5 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-navy-700/50 transition-all">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M4 10h16M6 21V10m12 11V10M8 6h8m-7 4V6a3 3 0 116 0v4"/></svg>
                    Add Section
                </button>
                <button @click="importSectionId = @js($selectedSectionId ? (string) $selectedSectionId : ''); importOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-white/15 px-5 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-navy-700/50 transition-all">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Students
                </button>
                <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    Add Student
                </button>
            </div>
        </div>

        {{-- Import modal --}}
        <div x-show="importOpen" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape="importOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="importOpen = false">
            <div x-show="importOpen" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-lg rounded-2xl bg-white dark:bg-navy-800 shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-6 py-5 bg-gradient-to-r from-purple-50 to-brand-50 dark:from-navy-800 dark:to-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Import Students</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select advisory section first, then upload the list.</p>
                    </div>
                    <button @click="importOpen = false" class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors hover:bg-gray-100 dark:hover:bg-navy-700 rounded-lg">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-3">
                        <p class="text-xs text-blue-700 dark:text-blue-300"><span class="font-semibold">Required columns:</span> first_name, last_name, gender. LRN and all other profile fields may be blank. Imported learners will be enrolled in the selected section.</p>
                    </div>

                    @if ($advisorySections->isEmpty())
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                            You do not have an advisory section in the active school year yet. Create or assign an advisory section before importing students.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('teacher.students.import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Advisory Section</label>
                            <select name="section_id" x-model="importSectionId" required @disabled($advisorySections->isEmpty())
                                    class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all disabled:opacity-60">
                                <option value="">Select section</option>
                                @foreach ($advisorySections as $section)
                                    <option value="{{ $section->id }}" @selected((int) old('section_id') === $section->id)>
                                        {{ $section->gradeLevel?->name }} - {{ $section->name }} ({{ $section->schoolYear?->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('section_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Student List File</label>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required @disabled($advisorySections->isEmpty())
                                   class="block w-full text-sm file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-600 file:text-white hover:file:bg-brand-700 file:cursor-pointer file:transition-colors dark:file:bg-brand-500 disabled:opacity-60">
                            @error('file')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-white/10">
                            <a href="{{ route('teacher.students.import.template') }}" class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download Template
                            </a>
                            <div class="flex gap-3">
                                <button type="button" @click="importOpen = false" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-navy-700 transition-colors">Cancel</button>
                                <button type="submit" @disabled($advisorySections->isEmpty()) class="rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-5 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all disabled:opacity-60 disabled:hover:shadow-none">Import</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add section modal --}}
        <div x-show="sectionOpen" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape="sectionOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="sectionOpen = false">
            <div x-show="sectionOpen" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-md rounded-2xl bg-white dark:bg-navy-800 shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-6 py-5 bg-gradient-to-r from-emerald-50 to-brand-50 dark:from-navy-800 dark:to-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add Advisory Section</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create the class where your students belong.</p>
                    </div>
                    <button @click="sectionOpen = false" class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors hover:bg-gray-100 dark:hover:bg-navy-700 rounded-lg">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('teacher.sections.store') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="return_to" value="students">

                    <div>
                        <label for="grade_level_id" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Grade Level</label>
                        <select id="grade_level_id" name="grade_level_id" required class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                            <option value="">Select grade level</option>
                            @foreach ($gradeLevels as $gradeLevel)
                                <option value="{{ $gradeLevel->id }}" @selected(old('grade_level_id') == $gradeLevel->id)>{{ $gradeLevel->name }}</option>
                            @endforeach
                        </select>
                        @error('grade_level_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Section Name</label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="50" placeholder="e.g. Jadeite"
                               class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="room" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Room</label>
                            <input id="room" name="room" value="{{ old('room') }}" maxlength="50"
                                   class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                            @error('room')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="capacity" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Capacity</label>
                            <input id="capacity" name="capacity" type="number" min="1" max="200" value="{{ old('capacity') }}"
                                   class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                            @error('capacity')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                        <button type="button" @click="sectionOpen = false" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-navy-700 transition-colors">Cancel</button>
                        <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-5 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">Create Section</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-3 shadow-sm dark:border-white/10 dark:bg-navy-800">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Advisory Sections</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Used for imports, attendance, SF forms, and QR cards.</p>
                </div>
            </div>

            <div class="pt-3">
                @if ($advisorySections->isEmpty())
                    <div class="flex flex-col gap-2 rounded-xl border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-xs text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 sm:flex-row sm:items-center sm:justify-between">
                        <span>No advisory section yet. Add one before importing students.</span>
                        <button type="button" @click="sectionOpen = true" class="shrink-0 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition-colors">Add Section</button>
                    </div>
                @else
                    <div class="flex gap-2 overflow-x-auto pb-1">
                        @foreach ($advisorySections as $section)
                            <div class="flex min-w-max items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-navy-900/60">
                                <div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">
                                        {{ $section->gradeLevel?->name }} - {{ $section->name }}
                                        <span class="ml-1 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-gray-500 dark:bg-navy-800 dark:text-gray-300">{{ $section->learners_count }} {{ \Illuminate\Support\Str::plural('learner', $section->learners_count) }}</span>
                                    </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $section->schoolYear?->name }}{{ $section->room ? ' • Room '.$section->room : '' }}{{ $section->capacity ? ' • Capacity '.$section->capacity : '' }}</p>
                                </div>
                                <a href="{{ route('qr-cards.section', $section) }}" class="rounded-lg border border-gray-300 px-2.5 py-1 text-[11px] font-semibold text-gray-700 hover:bg-white dark:border-white/15 dark:text-gray-200 dark:hover:bg-navy-700">QR</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">LRN</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Gender</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($students as $student)
                            @php
                                $currentSection = $student->currentEnrollment?->section;
                                $missing = collect([
                                    blank($student->lrn) ? 'LRN' : null,
                                    blank($student->birthdate) ? 'Birthdate' : null,
                                    blank($student->guardian_contact) ? 'Contact' : null,
                                    blank($student->address) && blank($student->address_barangay) ? 'Address' : null,
                                ])->filter()->values();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <code class="rounded-lg bg-gray-100 dark:bg-navy-900 px-2.5 py-1.5 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $student->lrn ?: '—' }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $student->full_name }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        @if ($currentSection)
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-navy-900 dark:text-slate-300">
                                                {{ $currentSection->gradeLevel?->name }} - {{ $currentSection->name }}
                                            </span>
                                        @endif
                                        @foreach ($missing->take(3) as $field)
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                                Missing {{ $field }}
                                            </span>
                                        @endforeach
                                        @if ($missing->count() > 3)
                                            <span class="text-[11px] text-gray-400">+{{ $missing->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $student->gender }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold
                                        {{ $student->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-gray-100 dark:bg-navy-700 text-gray-500 dark:text-gray-400' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('qr-cards.student', $student) }}" title="Download QR ID card"
                                           class="inline-flex items-center justify-center rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 14.25h1.5v1.5h-1.5zM17.25 14.25h1.5v1.5h-1.5zM14.25 17.25h1.5v1.5h-1.5zM17.25 17.25h1.5v1.5h-1.5z"/></svg>
                                        </a>
                                        <button type="button" @click='openEdit(@json($student))' title="Edit student"
                                                class="inline-flex items-center justify-center p-2 rounded-lg text-brand-600 hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-brand-500/10 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <x-confirm-delete :action="route('teacher.students.destroy', $student)" title="Delete student?" message="Delete {{ $student->full_name }}? This cannot be undone." />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No students yet</p>
                                <p class="mt-1 text-xs text-gray-500">Add your first learner to get started.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($students->hasPages())<div>{{ $students->links() }}</div>@endif

        {{-- Create / Edit modal --}}
        <x-form-modal create-title="Add Student" edit-title="Edit Student"
                      create-subtitle="Learner is added to your school's roster"
                      edit-subtitle="Update this learner"
                      submit-create="Add Student">
            <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">

            @if ($selectedSectionId && ($selectedSection = $advisorySections->firstWhere('id', $selectedSectionId)))
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                    New student will be enrolled into <span class="font-bold">{{ $selectedSection->gradeLevel?->name }} - {{ $selectedSection->name }}</span>.
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">LRN</label>
                    <input type="text" name="lrn" x-model="form.lrn" required inputmode="numeric" placeholder="12-digit LRN"
                           class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('lrn')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</label>
                    <select name="status" x-model="form.status" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        @foreach (['active', 'transferred', 'dropped', 'graduated', 'inactive'] as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">First Name</label>
                    <input type="text" name="first_name" x-model="form.first_name" required class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('first_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Last Name</label>
                    <input type="text" name="last_name" x-model="form.last_name" required class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('last_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Middle Name</label>
                    <input type="text" name="middle_name" x-model="form.middle_name" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Suffix</label>
                        <input type="text" name="suffix" x-model="form.suffix" placeholder="Jr., III" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Gender</label>
                        <select name="gender" x-model="form.gender" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Birthdate</label>
                    <input type="date" name="birthdate" x-model="form.birthdate" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    @error('birthdate')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Guardian Name</label>
                    <input type="text" name="guardian_name" x-model="form.guardian_name" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Guardian Contact</label>
                    <input type="text" name="guardian_contact" x-model="form.guardian_contact" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Guardian Relationship</label>
                    <input type="text" name="guardian_relationship" x-model="form.guardian_relationship" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>

            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Address</label>
                <input type="text" name="address" x-model="form.address" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
            </div>

            {{-- SF1 (School Register) profile --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Birth Place (Province)</label>
                    <input type="text" name="birth_place" x-model="form.birth_place" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Mother Tongue</label>
                    <input type="text" name="mother_tongue" x-model="form.mother_tongue" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">IP (Ethnic Group)</label>
                    <input type="text" name="ethnic_group" x-model="form.ethnic_group" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Religion</label>
                    <input type="text" name="religion" x-model="form.religion" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">House # / Street / Sitio / Purok</label>
                    <input type="text" name="address_street" x-model="form.address_street" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Barangay</label>
                    <input type="text" name="address_barangay" x-model="form.address_barangay" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Municipality / City</label>
                    <input type="text" name="address_municipality" x-model="form.address_municipality" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Province</label>
                    <input type="text" name="address_province" x-model="form.address_province" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Father's Name</label>
                    <input type="text" name="father_name" x-model="form.father_name" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Mother's Maiden Name</label>
                    <input type="text" name="mother_name" x-model="form.mother_name" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
            </div>
        </x-form-modal>
    </div>
</x-app-shell>
