@php $ended = ! $session->isActive(); @endphp
<x-app-shell title="Class in Session" :wide="true">
    <div @if (! $ended) x-data="{}" x-init="setTimeout(() => window.location.reload(), 15000)" @endif
         class="grid gap-6 lg:grid-cols-3">

        {{-- Left: the QR key + scanner handoff --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Class</p>
                <h2 class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                    {{ $session->section->gradeLevel->name }} — {{ $session->section->name }}
                </h2>
                @if ($session->subject)<p class="text-sm text-gray-500">{{ $session->subject->name }}</p>@endif
                <p class="mt-1 text-xs text-gray-400">{{ $session->session_date->format('l, F j, Y') }}</p>
            </div>

            @if ($ended)
                <div class="rounded-2xl border border-gray-300 dark:border-white/15 bg-gray-50 dark:bg-navy-800 p-6 text-center">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">This class has ended.</p>
                    <p class="mt-1 text-xs text-gray-500">Attendance is saved. Ended {{ $session->ended_at?->diffForHumans() }}.</p>
                </div>
            @else
                <div class="rounded-2xl border border-brand-200 dark:border-brand-500/30 bg-gradient-to-b from-brand-50 to-white dark:from-brand-500/10 dark:to-gray-800 p-6 text-center shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-300">Scanner key</p>
                    <p class="mt-2 select-all font-mono text-4xl font-extrabold tracking-[0.25em] text-gray-900 dark:text-white">{{ $session->qr_key }}</p>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Give this key to whoever runs the scanner. They open
                        <span class="font-semibold">{{ url('/class-scan') }}</span> and enter it to start scanning.
                    </p>
                    <a href="{{ route('class-scan.enter') }}" target="_blank"
                       class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/30 hover:from-brand-700 hover:to-brand-600 transition">
                        Open scanner on this device
                    </a>
                </div>

                <form method="POST" action="{{ route('class-sessions.end', $session) }}"
                      onsubmit="return confirm('End this class? The scanner key stops working.');">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-red-300 dark:border-red-500/40 px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                        End Class
                    </button>
                </form>
            @endif
        </div>

        {{-- Right: live roster --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-6 py-4">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Roster</h2>
                    <div class="flex items-center gap-3 text-sm">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1 font-semibold text-emerald-700 dark:text-emerald-300 tabular-nums">{{ $present }} present</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-navy-700 px-3 py-1 font-semibold text-gray-600 dark:text-gray-300 tabular-nums">{{ $total }} total</span>
                    </div>
                </div>
                <div class="max-h-[70vh] overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($roster as $enrollment)
                        @php
                            $mark = $marks->get($enrollment->id);
                            $isPresent = $mark && in_array($mark->status, \App\Models\Attendance::presentStatuses(), true);
                        @endphp
                        <div class="flex items-center justify-between px-6 py-3">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $enrollment->student->full_name }}</span>
                            @if ($isPresent)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Present
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 dark:bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-600 dark:text-red-300">Absent</span>
                            @endif
                        </div>
                    @empty
                        <p class="px-6 py-12 text-center text-sm text-gray-500">No learners are enrolled in this section yet.</p>
                    @endforelse
                </div>
            </div>
            @unless ($ended)
                <p class="mt-3 text-center text-xs text-gray-400">This page refreshes automatically as learners are scanned.</p>
            @endunless
        </div>
    </div>
</x-app-shell>
