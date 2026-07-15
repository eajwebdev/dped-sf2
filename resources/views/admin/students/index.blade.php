<x-admin-layout title="Students">
    <x-slot name="breadcrumbs">Admin / Students</x-slot>

    <div x-data="{
        createOpen: false,
        editOpen: false,
        editStudent: null,
        importOpen: false
    }" class="space-y-6">
        {{-- Toolbar --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search LRN or name…"
                           class="w-full sm:w-64 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm pl-4 pr-10 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    <option value="">All statuses</option>
                    @foreach (['active','transferred','dropped','graduated','inactive'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Search</button>
            </form>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a href="{{ route('admin.students.export', request()->only('q','status')) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" title="Export students">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export
                </a>
                <button @click="importOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" title="Import students">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </button>
                <button @click="createOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    New Student
                </button>
            </div>
        </div>

        {{-- Create Modal --}}
        <div x-show="createOpen" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape="createOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="createOpen = false">
            <div x-show="createOpen" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-gray-800 shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-5 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-800 dark:to-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New Student</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fill in the details below to register a new student</p>
                    </div>
                    <button @click="createOpen = false" class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    @include('admin.students.form-inline', ['student' => null])
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="editOpen" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape="editOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="editOpen = false">
            <div x-show="editOpen" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-gray-800 shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Student</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update student information below</p>
                    </div>
                    <button @click="editOpen = false" class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <template x-if="editStudent">
                        @include('admin.students.form-inline', ['student' => null])
                    </template>
                </div>
            </div>
        </div>

        {{-- Import Modal --}}
        <div x-show="importOpen" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape="importOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="importOpen = false">
            <div x-show="importOpen" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-5 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Import Students</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload a file to bulk import</p>
                    </div>
                    <button @click="importOpen = false" class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-3">
                        <p class="text-xs text-blue-700 dark:text-blue-300"><span class="font-semibold">Required columns:</span> lrn, first_name, middle_name, last_name, suffix, gender, birthdate, address, guardian_name, guardian_contact. Existing LRNs are skipped.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="relative">
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="block w-full text-sm
                              file:mr-4 file:py-2.5 file:px-4
                              file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-600 file:text-white
                              hover:file:bg-indigo-700
                              file:cursor-pointer file:transition-colors
                              dark:file:bg-indigo-500">
                            @error('file')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('admin.students.import.template') }}" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download Template
                            </a>
                            <div class="flex gap-3">
                                <button type="button" @click="importOpen = false" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                                <button type="submit" class="rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-5 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-indigo-500/30 transition-all">Import</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Students Table --}}
        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">Learner</th>
                            <th class="px-6 py-4">LRN</th>
                            <th class="px-6 py-4">Gender</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
                        @forelse ($students as $s)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($s->photo_path)
                                            <img src="{{ Storage::url($s->photo_path) }}" class="h-10 w-10 rounded-full object-cover shadow-sm" alt="">
                                        @else
                                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-sm font-bold text-white">{{ strtoupper(substr($s->first_name,0,1).substr($s->last_name,0,1)) }}</span>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.students.show', $s) }}" class="font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $s->full_name }}</a>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $s->gender }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-xs font-mono bg-gray-100 dark:bg-gray-900 px-2.5 py-1.5 rounded-lg text-gray-700 dark:text-gray-300">{{ $s->lrn }}</code>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $s->gender }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $badge = [
                                            'active' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/15', 'text' => 'text-emerald-700 dark:text-emerald-300', 'dot' => 'bg-emerald-500'],
                                            'transferred' => ['bg' => 'bg-blue-100 dark:bg-blue-500/15', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500'],
                                            'dropped' => ['bg' => 'bg-red-100 dark:bg-red-500/15', 'text' => 'text-red-700 dark:text-red-300', 'dot' => 'bg-red-500'],
                                            'graduated' => ['bg' => 'bg-indigo-100 dark:bg-indigo-500/15', 'text' => 'text-indigo-700 dark:text-indigo-300', 'dot' => 'bg-indigo-500'],
                                            'inactive' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'dot' => 'bg-gray-500'],
                                        ][$s->status] ?? ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'dot' => 'bg-gray-500'];
                                    @endphp
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                        {{ ucfirst($s->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.students.show', $s) }}" class="inline-flex items-center justify-center p-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors" title="View Student">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <button @click="editStudent = @json($s); editOpen = true" class="inline-flex items-center justify-center p-2.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors" title="Edit Student">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <x-delete-confirm-btn :action="route('admin.students.destroy', $s)" title="Delete Student" message="Are you sure you want to delete this student? This action cannot be undone." />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No learners found</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Try adjusting your search filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($students->hasPages())
            <div class="mt-6">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
