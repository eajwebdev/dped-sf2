<x-app-shell title="Attendance">
    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/50 px-6 py-4">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Select a class</h2>
            <a href="{{ route('portal') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                Open Scan Portal
            </a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-white/5">
            @forelse ($sections as $section)
                <div class="flex flex-col gap-3 px-6 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-navy-700/30 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('attendance.sheet', ['section' => $section, 'date' => $date->toDateString()]) }}" class="group min-w-0 flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $section->gradeLevel->name }} — {{ $section->name }}</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">SY {{ $section->schoolYear->name }} · Adviser: {{ $section->adviser?->full_name ?? '—' }}</p>
                    </a>
                    <div class="flex items-center gap-2 self-end sm:self-auto">
                        <a href="{{ route('qr-cards.section', $section) }}"
                           class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 dark:border-white/15 px-3.5 py-2 text-sm font-medium hover:bg-gray-50 dark:hover:bg-navy-700/50 transition-colors"
                           title="Download all learners' QR codes (images, .zip)">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4m-9 8h10"/></svg>
                            All QR
                        </a>
                        <a href="{{ route('attendance.sheet', ['section' => $section, 'date' => $date->toDateString()]) }}"
                           class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-4 py-2 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all">
                            Open Sheet
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                    <svg class="mb-3 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No accessible classes</p>
                    <p class="mt-1 text-xs text-gray-500">You have no advisory or subject-teaching assignments for the active school year.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-shell>
