<x-admin-layout title="Search">
    <x-slot name="breadcrumbs">Admin / Search</x-slot>

    <form method="GET" action="{{ route('admin.search.index') }}" class="mb-5">
        <input type="search" name="q" value="{{ $q }}" autofocus placeholder="Search students, LRN, teachers, sections, subjects…"
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
    </form>

    @if (strlen($q) < 2)
        <x-card><p class="py-8 text-center text-gray-400">Type at least 2 characters to search.</p></x-card>
    @else
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Students ({{ $results['students']->count() }})">
                @forelse ($results['students'] as $s)
                    <a href="{{ route('admin.students.show', $s) }}" class="flex items-center justify-between rounded-lg px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <span class="text-sm">{{ $s->full_name }}</span><span class="font-mono text-xs text-gray-400">{{ $s->lrn }}</span>
                    </a>
                @empty <p class="text-sm text-gray-400">No students.</p> @endforelse
            </x-card>
            <x-card title="Teachers ({{ $results['teachers']->count() }})">
                @forelse ($results['teachers'] as $t)
                    <a href="{{ route('admin.teachers.edit', $t) }}" class="block rounded-lg px-2 py-1.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/40">{{ $t->full_name }}</a>
                @empty <p class="text-sm text-gray-400">No teachers.</p> @endforelse
            </x-card>
            <x-card title="Sections ({{ $results['sections']->count() }})">
                @forelse ($results['sections'] as $sec)
                    <a href="{{ route('admin.assignments.index', $sec) }}" class="block rounded-lg px-2 py-1.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/40">{{ $sec->gradeLevel->name }} — {{ $sec->name }} <span class="text-xs text-gray-400">({{ $sec->schoolYear->name }})</span></a>
                @empty <p class="text-sm text-gray-400">No sections.</p> @endforelse
            </x-card>
            <x-card title="Subjects ({{ $results['subjects']->count() }})">
                @forelse ($results['subjects'] as $sub)
                    <a href="{{ route('admin.subjects.edit', $sub) }}" class="block rounded-lg px-2 py-1.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/40">{{ $sub->name }} <span class="text-xs text-gray-400">({{ $sub->code }})</span></a>
                @empty <p class="text-sm text-gray-400">No subjects.</p> @endforelse
            </x-card>
        </div>
    @endif
</x-admin-layout>
