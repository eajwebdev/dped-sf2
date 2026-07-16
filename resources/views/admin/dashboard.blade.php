<x-admin-layout title="Dashboard">
    @php
        $gradeMax = collect($byGrade)->max('value') ?: 1;
        $trendMax = collect($trend)->max('present') ?: 1;
    @endphp

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="animate-slide-up">
            <x-stat-card label="Active School Year" :value="$cards['schoolYear']" tone="brand" :animate="false"
                         icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </div>
        <div class="stagger-1 animate-slide-up">
            <x-stat-card label="Enrolled Students" :value="number_format($cards['students'])" tone="info" :href="route('admin.students.index')"
                         icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </div>
        <div class="stagger-2 animate-slide-up">
            <x-stat-card label="Active Teachers" :value="number_format($cards['teachers'])" tone="navy" :href="route('admin.teachers.index')"
                         icon="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
        </div>
        <div class="stagger-3 animate-slide-up">
            <x-stat-card label="Sections" :value="number_format($cards['sections'])" tone="success" :href="route('admin.sections.index')"
                         icon="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="stagger-2 mt-6 flex animate-slide-up flex-wrap gap-3">
        @foreach ([
            ['route' => 'admin.students.index', 'label' => 'Add Student', 'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z'],
            ['route' => 'admin.registrations.index', 'label' => 'Review Registrations', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['route' => 'attendance.index', 'label' => 'View Attendance', 'icon' => 'M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12'],
            ['route' => 'reports.sf2.index', 'label' => 'SF2 Report', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
        ] as $qa)
            <a href="{{ route($qa['route']) }}" class="btn-outline btn-sm">
                <svg class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $qa['icon'] }}"/></svg>
                {{ $qa['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Today --}}
    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="stagger-3 animate-slide-up lg:col-span-1">
            <x-card title="Today's Attendance" accent class="h-full">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-emerald-50 p-4 text-center dark:bg-emerald-500/10">
                        <p class="text-2xl font-extrabold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $today['present'] }}</p>
                        <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">Present</p>
                    </div>
                    <div class="rounded-2xl bg-red-50 p-4 text-center dark:bg-red-500/10">
                        <p class="text-2xl font-extrabold tabular-nums text-red-600 dark:text-red-400">{{ $today['absent'] }}</p>
                        <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">Absent</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-4 text-center dark:bg-amber-500/10">
                        <p class="text-2xl font-extrabold tabular-nums text-amber-500 dark:text-amber-400">{{ $today['late'] }}</p>
                        <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">Late</p>
                    </div>
                    <div class="rounded-2xl bg-brand-50 p-4 text-center dark:bg-brand-500/10">
                        <p class="text-2xl font-extrabold tabular-nums text-brand-600 dark:text-brand-400">{{ $today['completion'] }}%</p>
                        <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">Marked</p>
                    </div>
                </div>
                <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                    <div class="h-2 rounded-full bg-gradient-to-r from-brand-500 to-brand-400 transition-all duration-1000"
                         style="width: {{ min(100, $today['completion']) }}%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">{{ $today['enrolled'] }} learners enrolled this year</p>
            </x-card>
        </div>

        {{-- Attendance trend --}}
        <div class="stagger-4 animate-slide-up lg:col-span-2">
            <x-card title="Attendance — last {{ count($trend) }} class days" class="h-full">
                @if (count($trend))
                    <div class="flex h-44 items-end gap-2 sm:gap-3">
                        @foreach ($trend as $t)
                            <div class="group flex flex-1 flex-col items-center justify-end gap-1.5">
                                <span class="text-[10px] font-semibold text-slate-400 opacity-0 transition-opacity group-hover:opacity-100">{{ $t['rate'] }}%</span>
                                <div class="w-full rounded-t-lg bg-gradient-to-t from-brand-600 to-brand-400 opacity-80 transition-all duration-300 group-hover:opacity-100 group-hover:shadow-glow-pink-sm"
                                     style="height: {{ max(4, round($t['present'] / $trendMax * 130)) }}px"
                                     title="{{ $t['present'] }} present ({{ $t['rate'] }}%)"></div>
                                <span class="whitespace-nowrap text-[10px] text-slate-400">{{ $t['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state title="No attendance data yet"
                                   description="Attendance appears here once teachers start scanning."
                                   icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                @endif
            </x-card>
        </div>
    </div>

    {{-- Enrollment by grade --}}
    <div class="stagger-5 mt-5 animate-slide-up">
        <x-card title="Enrollment by Grade Level">
            @if (count($byGrade))
                <div class="space-y-2.5">
                    @foreach ($byGrade as $g)
                        <div class="group flex items-center gap-3">
                            <span class="w-20 shrink-0 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $g['label'] }}</span>
                            <div class="h-6 flex-1 overflow-hidden rounded-lg bg-slate-100 dark:bg-white/5">
                                <div class="flex h-6 items-center justify-end rounded-lg bg-gradient-to-r from-navy-500 to-brand-500 px-2.5 transition-all duration-500 group-hover:brightness-110"
                                     style="width: {{ max(5, round($g['value'] / $gradeMax * 100)) }}%">
                                    <span class="text-[10px] font-bold text-white">{{ $g['value'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state title="No enrollments yet"
                               description="No enrollments in the active school year."
                               icon="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
            @endif
        </x-card>
    </div>
</x-admin-layout>
