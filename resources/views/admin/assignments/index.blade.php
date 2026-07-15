<x-admin-layout title="Subjects & Teachers">
    <x-slot name="breadcrumbs"><a href="{{ route('admin.sections.index') }}" class="hover:underline">Sections</a> / {{ $section->gradeLevel->name }} {{ $section->name }} / Assignments</x-slot>

    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold">{{ $section->gradeLevel->name }} — {{ $section->name }}</h2>
            <p class="text-xs text-gray-400">SY {{ $section->schoolYear->name }} · Adviser: {{ $section->adviser?->full_name ?? '—' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Offerings + teachers --}}
        <div class="lg:col-span-2 space-y-3">
            @forelse ($section->subjectAssignments as $offering)
                <x-card>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-semibold">{{ $offering->subject->name }} <span class="text-xs font-normal text-gray-400">({{ $offering->subject->code }})</span></h3>

                            {{-- Assigned teachers --}}
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse ($offering->teacherAssignments as $ta)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-1 text-xs text-indigo-700 dark:text-indigo-300">
                                        {{ $ta->teacher->full_name }}{{ $ta->is_primary ? ' ★' : '' }}
                                        <form method="POST" action="{{ route('admin.assignments.teachers.destroy', $ta) }}">
                                            @csrf @method('DELETE')
                                            <button class="text-indigo-400 hover:text-red-500" title="Unassign">&times;</button>
                                        </form>
                                    </span>
                                @empty
                                    <span class="text-xs text-amber-500">No teacher assigned yet.</span>
                                @endforelse
                            </div>

                            {{-- Assign a teacher --}}
                            <form method="POST" action="{{ route('admin.assignments.teachers.store', $offering) }}" class="mt-3 flex flex-wrap items-center gap-2">
                                @csrf
                                <select name="teacher_id" required class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-xs py-1.5 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Assign teacher…</option>
                                    @foreach ($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                                <label class="flex items-center gap-1 text-xs text-gray-500"><input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500"> Primary</label>
                                <button class="rounded-lg bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs font-medium">Add</button>
                            </form>
                        </div>
                        <x-confirm-delete :action="route('admin.assignments.subjects.destroy', $offering)" title="Remove subject?" message="Remove {{ $offering->subject->name }} from this section? Teacher assignments will be removed too." />
                    </div>
                </x-card>
            @empty
                <x-card><p class="py-6 text-center text-gray-400">No subjects offered in this section yet. Add one from the panel.</p></x-card>
            @endforelse
        </div>

        {{-- Add subject --}}
        <div>
            <x-card title="Add Subject">
                @if ($availableSubjects->isEmpty())
                    <p class="text-sm text-gray-400">All eligible subjects are already offered. Create more under <a href="{{ route('admin.subjects.index') }}" class="text-indigo-600 hover:underline">Subjects</a>.</p>
                @else
                    <form method="POST" action="{{ route('admin.assignments.subjects.store', $section) }}" class="space-y-3">
                        @csrf
                        <x-form.select label="Subject" name="subject_id" required placeholder="— Select subject —">
                            @foreach ($availableSubjects as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->name }} ({{ $subj->code }})</option>
                            @endforeach
                        </x-form.select>
                        <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Add to section</button>
                    </form>
                @endif
            </x-card>
        </div>
    </div>
</x-admin-layout>
