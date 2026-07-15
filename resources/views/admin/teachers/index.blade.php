@php
    $teacherRow = fn ($t) => [
        'id' => $t->id,
        'first_name' => $t->first_name, 'middle_name' => $t->middle_name,
        'last_name' => $t->last_name, 'suffix' => $t->suffix,
        'gender' => $t->gender, 'employee_no' => $t->employee_no,
        'email' => $t->email, 'contact' => $t->contact,
        'is_active' => (bool) $t->is_active,
        'create_account' => (bool) $t->user_id, 'has_account' => (bool) $t->user_id,
        'account_email' => $t->user?->email,
    ];
    $editRow = isset($editModel) ? $teacherRow($editModel) : null;
@endphp

<x-admin-layout title="Teachers">
    <x-slot name="breadcrumbs">Admin / Teachers</x-slot>

    <div x-data="resourceModal({
            base: '{{ url('admin/teachers') }}',
            defaults: { first_name: '', middle_name: '', last_name: '', suffix: '', gender: '', employee_no: '', email: '', contact: '', is_active: true, create_account: false, has_account: false, account_email: '', account_password: '' },
            autoOpen: @js($openModal ?? null),
            editRow: @js($editRow),
            reopen: @js($errors->any() ? ['id' => old('_edit_id') ?: null, 'old' => old()] : null),
         })" class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">Faculty records. A teacher may have a linked login account.</p>
            <button @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                New Teacher
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Emp. No</th>
                            <th class="px-6 py-4">Login</th>
                            <th class="px-6 py-4 text-center">Advises</th>
                            <th class="px-6 py-4 text-center">Subjects</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
                        @forelse ($teachers as $t)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $t->full_name }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $t->employee_no ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if ($t->user)<span class="text-emerald-600 dark:text-emerald-400">{{ $t->user->email }}</span>@else<span class="text-gray-400">No account</span>@endif
                                </td>
                                <td class="px-6 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400">{{ $t->advised_sections_count }}</td>
                                <td class="px-6 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400">{{ $t->subject_assignments_count }}</td>
                                <td class="px-6 py-4">
                                    @if ($t->is_active)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300"><span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click='openEdit(@json($teacherRow($t)))' title="Edit teacher"
                                                class="inline-flex items-center justify-center p-2 rounded-lg text-indigo-600 hover:bg-indigo-50 hover:text-indigo-700 dark:text-indigo-400 dark:hover:bg-indigo-500/10 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if ($t->advised_sections_count === 0 && $t->subject_assignments_count === 0)
                                            <x-confirm-delete :action="route('admin.teachers.destroy', $t)" title="Delete teacher?" message="Delete {{ $t->full_name }}? This cannot be undone." />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No teachers yet</p>
                                <p class="mt-1 text-xs text-gray-500">Add your first faculty member with the New Teacher button.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($teachers->hasPages())<div>{{ $teachers->links() }}</div>@endif

        {{-- Create / Edit modal --}}
        <x-form-modal create-title="New Teacher" edit-title="Edit Teacher"
                      create-subtitle="Add a faculty member, optionally with a login"
                      edit-subtitle="Update this faculty member"
                      submit-create="Create Teacher" submit-edit="Save Teacher" max-width="max-w-3xl">
            <div>
                <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Personal</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">First Name</label>
                        <input type="text" name="first_name" x-model="form.first_name" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        @error('first_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Middle Name</label>
                        <input type="text" name="middle_name" x-model="form.middle_name" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Last Name</label>
                        <input type="text" name="last_name" x-model="form.last_name" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        @error('last_name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Suffix</label>
                        <input type="text" name="suffix" x-model="form.suffix" placeholder="Jr., III" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Gender</label>
                        <select name="gender" x-model="form.gender" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                            <option value="">—</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Employee No.</label>
                        <input type="text" name="employee_no" x-model="form.employee_no" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Email</label>
                        <input type="email" name="email" x-model="form.email" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Contact</label>
                        <input type="text" name="contact" x-model="form.contact" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>
                </div>
                <label class="mt-4 inline-flex items-center gap-2.5 text-sm">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500/20">
                    Active
                </label>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Login Account</h4>
                <label class="flex cursor-pointer items-center gap-3">
                    <input type="hidden" name="create_account" value="0">
                    <input type="checkbox" name="create_account" value="1" x-model="form.create_account" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500/20">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="form.has_account ? 'Update the linked login account' : 'Create a login account for this teacher'"></span>
                </label>
                <div x-show="form.create_account" x-cloak class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Account Email</label>
                        <input type="email" name="account_email" x-model="form.account_email" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        @error('account_email')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Password</label>
                        <input type="password" name="account_password" x-model="form.account_password" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        <p class="mt-1 text-[11px] text-gray-400" x-text="form.has_account ? 'Leave blank to keep current password' : 'Minimum 8 characters'"></p>
                        @error('account_password')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </x-form-modal>
    </div>
</x-admin-layout>
