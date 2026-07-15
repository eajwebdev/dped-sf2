<x-admin-layout title="Dashboard">
    @php
        $cardDefs = [
            ['label' => 'Active School Year', 'value' => $cards['schoolYear'], 'grad' => 'from-indigo-500 to-indigo-600'],
            ['label' => 'Enrolled Students', 'value' => number_format($cards['students']), 'grad' => 'from-blue-500 to-blue-600'],
            ['label' => 'Active Teachers', 'value' => number_format($cards['teachers']), 'grad' => 'from-violet-500 to-violet-600'],
            ['label' => 'Sections', 'value' => number_format($cards['sections']), 'grad' => 'from-emerald-500 to-emerald-600'],
        ];
        $gradeMax = collect($byGrade)->max('value') ?: 1;
        $trendMax = collect($trend)->max('present') ?: 1;
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cardDefs as $c)
            <div class="rounded-xl bg-gradient-to-br {{ $c['grad'] }} p-5 text-white shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-white/80">{{ $c['label'] }}</p>
                <p class="mt-2 text-3xl font-bold">{{ $c['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Today --}}
    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-card title="Today's Attendance" class="lg:col-span-1">
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-emerald-50 dark:bg-emerald-500/10 p-3 text-center">
                    <p class="text-2xl font-bold text-emerald-600">{{ $today['present'] }}</p><p class="text-xs text-gray-500">Present</p>
                </div>
                <div class="rounded-lg bg-red-50 dark:bg-red-500/10 p-3 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $today['absent'] }}</p><p class="text-xs text-gray-500">Absent</p>
                </div>
                <div class="rounded-lg bg-amber-50 dark:bg-amber-500/10 p-3 text-center">
                    <p class="text-2xl font-bold text-amber-500">{{ $today['late'] }}</p><p class="text-xs text-gray-500">Late</p>
                </div>
                <div class="rounded-lg bg-indigo-50 dark:bg-indigo-500/10 p-3 text-center">
                    <p class="text-2xl font-bold text-indigo-600">{{ $today['completion'] }}%</p><p class="text-xs text-gray-500">Marked</p>
                </div>
            </div>
            <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-2 rounded-full bg-indigo-500" style="width: {{ min(100, $today['completion']) }}%"></div>
            </div>
            <p class="mt-1 text-xs text-gray-400">{{ $today['enrolled'] }} learners enrolled this year</p>
        </x-card>

        {{-- Attendance trend --}}
        <x-card title="Attendance — last {{ count($trend) }} class days" class="lg:col-span-2">
            @if (count($trend))
                <div class="flex items-end gap-2 sm:gap-3 h-40">
                    @foreach ($trend as $t)
                        <div class="flex flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-[10px] text-gray-400">{{ $t['rate'] }}%</span>
                            <div class="w-full rounded-t bg-indigo-500/80 hover:bg-indigo-500 transition-all"
                                 style="height: {{ max(3, round($t['present'] / $trendMax * 130)) }}px" title="{{ $t['present'] }} present"></div>
                            <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $t['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-10 text-center text-sm text-gray-400">No attendance data yet.</p>
            @endif
        </x-card>
    </div>

    {{-- Enrollment by grade --}}
    <div class="mt-4">
        <x-card title="Enrollment by Grade Level">
            @if (count($byGrade))
                <div class="space-y-2">
                    @foreach ($byGrade as $g)
                        <div class="flex items-center gap-3">
                            <span class="w-20 shrink-0 text-xs text-gray-500">{{ $g['label'] }}</span>
                            <div class="h-5 flex-1 rounded bg-gray-100 dark:bg-gray-700/50">
                                <div class="flex h-5 items-center justify-end rounded bg-gradient-to-r from-blue-500 to-indigo-500 px-2"
                                     style="width: {{ max(4, round($g['value'] / $gradeMax * 100)) }}%">
                                    <span class="text-[10px] font-semibold text-white">{{ $g['value'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-8 text-center text-sm text-gray-400">No enrollments in the active school year.</p>
            @endif
        </x-card>
    </div>
</x-admin-layout>
