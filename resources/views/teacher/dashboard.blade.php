<x-app-shell title="My Dashboard">
    <div class="space-y-6">
        {{-- Stat tiles --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 p-5 text-white shadow-md shadow-indigo-500/20">
                <p class="text-xs font-bold uppercase tracking-wider text-white/70">My Classes</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ $sections->count() }}</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 text-white shadow-md shadow-emerald-500/20">
                <p class="text-xs font-bold uppercase tracking-wider text-white/70">Marked Today</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ $markedToday }}<span class="text-lg font-medium text-white/70">/{{ $sections->count() }}</span></p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 p-5 text-white shadow-md shadow-violet-500/20">
                <p class="text-xs font-bold uppercase tracking-wider text-white/70">Today</p>
                <p class="mt-2 text-xl font-bold">{{ $today->format('D, M d') }}</p>
            </div>
            <a href="{{ route('portal') }}" class="group flex flex-col justify-between rounded-2xl border-2 border-dashed border-indigo-300 dark:border-indigo-500/40 p-5 transition-all hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-500/10">
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400">Quick Launch</p>
                <p class="mt-2 flex items-center gap-2 text-lg font-bold text-indigo-600 dark:text-indigo-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                    Scan Portal
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </p>
            </a>
        </div>

        {{-- Class list --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-6 py-4">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">My Classes</h2>
                <a href="{{ route('schedule.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Manage weekly schedule
                </a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                @forelse ($sections as $section)
                    @php $isAdviser = $section->adviser_id === auth()->user()->teacher?->id; @endphp
                    <div class="flex flex-col gap-3 px-6 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $isAdviser ? 'from-indigo-500 to-indigo-700' : 'from-sky-500 to-sky-700' }} text-sm font-bold text-white shadow-sm">
                                {{ preg_replace('/[^0-9]/', '', $section->gradeLevel->name) ?: strtoupper(substr($section->gradeLevel->name, 0, 2)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $section->gradeLevel->name }} — {{ $section->name }}</p>
                                <p class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="tabular-nums">{{ $section->learners_count }} learners</span>
                                    @if ($isAdviser)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2 py-0.5 font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">Adviser</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">Subject Teacher</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <a href="{{ route('qr-cards.section', $section) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 px-3.5 py-2 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                               title="Download printable QR ID cards for this class">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4m-9 8h10"/></svg>
                                QR IDs
                            </a>
                            <a href="{{ route('attendance.sheet', $section) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-2 text-sm font-bold text-white hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Take Attendance
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                        <svg class="mb-3 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No assigned classes yet</p>
                        <p class="mt-1 max-w-sm text-xs text-gray-500">Ask an administrator to assign you as a class adviser or subject teacher for the active school year.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-shell>
