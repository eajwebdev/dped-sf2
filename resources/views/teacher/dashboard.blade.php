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
            {{-- Cutting classes: advisory learners seen in one period but missing from another --}}
            <a href="{{ route('teacher.cutting.index') }}"
               class="stagger-3 group flex animate-slide-up flex-col justify-between rounded-card border p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lift
                      {{ $cuttingToday > 0
                            ? 'border-amber-300 bg-amber-50/60 dark:border-amber-500/40 dark:bg-amber-500/10'
                            : 'border-slate-200/80 bg-white dark:border-white/10 dark:bg-navy-800' }}">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-bold uppercase tracking-wider {{ $cuttingToday > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }}">Cut classes today</p>
                    <svg class="h-5 w-5 shrink-0 {{ $cuttingToday > 0 ? 'text-amber-500' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <p class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tabular-nums {{ $cuttingToday > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-slate-900 dark:text-white' }}">{{ $cuttingToday }}</span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        {{ Str::plural('learner', $cuttingToday) }}
                        <span class="inline-flex items-center gap-0.5 font-semibold text-brand-500 transition-transform group-hover:translate-x-0.5">· view</span>
                    </span>
                </p>
            </a>
        </div>

        {{-- Class list --}}
        <div class="stagger-2 animate-slide-up" x-data="{ addClass: {{ $errors->any() ? 'true' : 'false' }} }">
            <x-card :padding="false">
                <x-slot:title>My Classes</x-slot:title>
                <x-slot:actions>
                    <div class="flex items-center gap-4">
                        <button type="button" @click="addClass = true"
                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-500 transition-colors hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Add class
                        </button>
                        <a href="{{ route('schedule.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-500 transition-colors hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Manage weekly schedule
                        </a>
                    </div>
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
                        <x-empty-state title="No classes yet"
                                       description="Add your advisory class to start building a roster — or ask an administrator to assign you as a subject teacher."
                                       icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    @endforelse
                </div>
            </x-card>

            {{-- Add advisory class --}}
            <div x-show="addClass" x-cloak @keydown.escape.window="addClass = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-navy-950/60 backdrop-blur-sm" @click="addClass = false"></div>
                <div class="relative w-full max-w-md rounded-card border border-slate-200 bg-white p-6 shadow-lift dark:border-white/10 dark:bg-navy-800"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add a class</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        You become the class adviser. It is created in the active school year.
                    </p>

                    <form method="POST" action="{{ route('teacher.sections.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="grade_level_id" class="label">Grade level <span class="text-brand-500">*</span></label>
                            <select id="grade_level_id" name="grade_level_id" required class="input @error('grade_level_id') input-error @enderror">
                                <option value="">— Select grade level —</option>
                                @foreach ($gradeLevels as $g)
                                    <option value="{{ $g->id }}" @selected(old('grade_level_id') == $g->id)>{{ $g->name }}</option>
                                @endforeach
                            </select>
                            @error('grade_level_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="name" class="label">Section name <span class="text-brand-500">*</span></label>
                            <input id="name" name="name" value="{{ old('name') }}" required maxlength="50"
                                   placeholder="e.g. Rizal" class="input @error('name') input-error @enderror">
                            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="room" class="label">Room</label>
                                <input id="room" name="room" value="{{ old('room') }}" maxlength="50" class="input">
                            </div>
                            <div>
                                <label for="capacity" class="label">Capacity</label>
                                <input id="capacity" name="capacity" type="number" min="1" max="200" value="{{ old('capacity') }}" class="input">
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="addClass = false" class="btn-ghost btn-md">Cancel</button>
                            <button type="submit" class="btn-primary btn-md">Create class</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
