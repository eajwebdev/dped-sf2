<x-app-shell title="My Dashboard">
    <div class="space-y-6">
        {{-- Stat tiles --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="animate-slide-up">
                <x-stat-card label="My Classes" :value="$sections->count()" tone="brand"
                             icon="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
            </div>
            <div class="stagger-1 animate-slide-up">
                <x-stat-card label="Marked Today" :value="$markedToday . ' / ' . $sections->count()" tone="success" :animate="false"
                             icon="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </div>
            <div class="stagger-2 animate-slide-up">
                <x-stat-card label="Today" :value="$today->format('D, M d')" tone="navy" :animate="false"
                             icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </div>
            <a href="{{ route('portal') }}"
               class="stagger-3 group flex animate-slide-up flex-col justify-between rounded-card border-2 border-dashed border-brand-300 p-5 transition-all duration-300 hover:-translate-y-1 hover:border-brand-500 hover:bg-brand-50 hover:shadow-glow-pink-sm dark:border-brand-500/40 dark:hover:bg-brand-500/10">
                <p class="text-xs font-bold uppercase tracking-wider text-brand-500 dark:text-brand-400">Quick Launch</p>
                <p class="mt-2 flex items-center gap-2 text-lg font-bold text-brand-600 dark:text-brand-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                    Scan Portal
                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </p>
            </a>
        </div>

        {{-- Class list --}}
        <div class="stagger-2 animate-slide-up">
            <x-card :padding="false">
                <x-slot:title>My Classes</x-slot:title>
                <x-slot:actions>
                    <a href="{{ route('schedule.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-500 transition-colors hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Manage weekly schedule
                    </a>
                </x-slot:actions>

                <div class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($sections as $section)
                        @php $isAdviser = $section->adviser_id === auth()->user()->teacher?->id; @endphp
                        <div class="flex flex-col gap-3 px-6 py-4 transition-colors hover:bg-slate-50/80 dark:hover:bg-white/5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $isAdviser ? 'from-brand-500 to-brand-700 shadow-glow-pink-sm' : 'from-navy-500 to-navy-700' }} text-sm font-bold text-white">
                                    {{ preg_replace('/[^0-9]/', '', $section->gradeLevel->name) ?: strtoupper(substr($section->gradeLevel->name, 0, 2)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $section->gradeLevel->name }} — {{ $section->name }}</p>
                                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                        <span class="tabular-nums">{{ $section->learners_count }} learners</span>
                                        @if ($isAdviser)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">Adviser</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-navy-50 px-2 py-0.5 font-semibold text-navy-600 dark:bg-navy-500/20 dark:text-navy-200">Subject Teacher</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <a href="{{ route('qr-cards.section', $section) }}"
                                   class="btn-outline btn-sm"
                                   title="Download all learners' QR codes as images (.zip)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4m-9 8h10"/></svg>
                                    All QR
                                </a>
                                <a href="{{ route('attendance.sheet', $section) }}"
                                   class="btn-outline btn-sm">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Attendance
                                </a>
                                <form method="POST" action="{{ route('class-sessions.start') }}">
                                    @csrf
                                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                                    <button type="submit"
                                            class="btn btn-sm bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-[0_4px_14px_-2px_rgb(34_197_94/0.35)] hover:from-emerald-600 hover:to-emerald-700"
                                            title="Start class and generate a QR scanning key">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Start Class
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="No assigned classes yet"
                                       description="Ask an administrator to assign you as a class adviser or subject teacher for the active school year."
                                       icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</x-app-shell>
