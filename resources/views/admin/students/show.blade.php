<x-admin-layout :title="$student->full_name">
    <x-slot name="breadcrumbs"><a href="{{ route('admin.students.index') }}" class="hover:underline">Students</a> / {{ $student->full_name }}</x-slot>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Profile --}}
        <div class="lg:col-span-2 space-y-4">
            <x-card>
                <x-slot name="actions">
                    <x-action icon="qr" :href="route('qr-cards.student', $student)" title="Download QR image" color="gray" />
                    <x-action icon="edit" :href="route('admin.students.edit', $student)" title="Edit student" color="indigo" />
                </x-slot>
                <div class="flex items-start gap-5">
                    @if ($student->photo_path)
                        <img src="{{ Storage::url($student->photo_path) }}" class="h-24 w-24 rounded-xl object-cover" alt="">
                    @else
                        <span class="flex h-24 w-24 items-center justify-center rounded-xl bg-gray-200 dark:bg-navy-700 text-2xl font-semibold">{{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}</span>
                    @endif
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold">{{ $student->full_name }}</h2>
                        <p class="font-mono text-sm text-gray-500">LRN {{ $student->lrn }}</p>
                        <dl class="mt-3 grid grid-cols-2 gap-x-6 gap-y-1 text-sm">
                            <div><dt class="text-gray-400 text-xs">Gender</dt><dd>{{ $student->gender }}</dd></div>
                            <div><dt class="text-gray-400 text-xs">Birthdate</dt><dd>{{ $student->birthdate?->format('M d, Y') ?? '—' }}</dd></div>
                            <div><dt class="text-gray-400 text-xs">Guardian</dt><dd>{{ $student->guardian_name ?? '—' }}</dd></div>
                            <div><dt class="text-gray-400 text-xs">Contact</dt><dd>{{ $student->guardian_contact ?? '—' }}</dd></div>
                            <div class="col-span-2"><dt class="text-gray-400 text-xs">Address</dt><dd>{{ $student->address ?? '—' }}</dd></div>
                        </dl>
                    </div>
                </div>
            </x-card>

            <x-card title="Enrollment History">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                        <thead><tr class="text-left text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2">School Year</th><th class="px-3 py-2">Grade</th><th class="px-3 py-2">Section</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Enrolled</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse ($student->enrollments as $e)
                                <tr>
                                    <td class="px-3 py-2 font-medium">{{ $e->schoolYear->name }}{{ $e->schoolYear->is_active ? ' (active)' : '' }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $e->gradeLevel->name }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $e->section->name }}</td>
                                    <td class="px-3 py-2"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] capitalize dark:bg-navy-700">{{ str_replace('_',' ',$e->status) }}</span></td>
                                    <td class="px-3 py-2 text-gray-500">{{ $e->enrollment_date->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-8 text-center text-gray-400">No enrollments yet. Enroll this learner from the Enrollment page.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- QR + status --}}
        <div class="space-y-4">
            <x-card title="Learner QR">
                <div class="flex flex-col items-center">
                    <img src="{{ $qrDataUri }}" alt="QR code" class="h-44 w-44 rounded-lg bg-white p-2">
                    <p class="mt-3 text-center text-xs text-gray-400 break-all">{{ $student->qr_token }}</p>
                    <p class="mt-1 text-center text-[11px] text-gray-400">Ready for future QR-based attendance.</p>
                </div>
            </x-card>
        </div>
    </div>
</x-admin-layout>
