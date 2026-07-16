<x-app-shell title="My Students" :wide="true">
    <div x-data="resourceModal({
            base: '{{ url('students') }}',
            defaults: { lrn: '', first_name: '', middle_name: '', last_name: '', suffix: '', gender: 'Male', birthdate: '', address: '', guardian_name: '', guardian_contact: '', status: 'active' },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editModel ?? null),
            reopen: @js($errors->any() ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         })" class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="relative w-full sm:max-w-xs">
                <input type="search" name="q" value="{{ $search }}" placeholder="Search name or LRN…"
                       class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm pl-4 pr-10 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>
            <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                Add Student
            </button>
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
                            <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30 transition-colors">
                                <td class="px-6 py-4"><code class="rounded-lg bg-gray-100 dark:bg-navy-900 px-2.5 py-1.5 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $student->lrn }}</code></td>
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $student->full_name }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $student->gender }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold
                                        {{ $student->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-gray-100 dark:bg-navy-700 text-gray-500 dark:text-gray-400' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
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
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Address</label>
                <input type="text" name="address" x-model="form.address" class="w-full rounded-lg border border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm px-4 py-2.5 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
            </div>
        </x-form-modal>
    </div>
</x-app-shell>
