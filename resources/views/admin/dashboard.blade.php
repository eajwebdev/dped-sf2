<x-admin-layout title="Dashboard">
    {{-- ═══════════ SALES ═══════════ --}}
    <div class="mb-8 space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">Sales</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    ₱{{ number_format($sales['effectivePriceCentavos'] / 100, 2) }}/month per teacher
                    @if ($sales['discountPercent'] > 0)
                        <span class="ml-1 rounded-full bg-brand-50 px-2 py-0.5 font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                            {{ $sales['discountPercent'] }}% off ₱{{ number_format($sales['priceCentavos'] / 100, 2) }}
                        </span>
                    @endif
                    @if (($sales['freeCount'] ?? 0) > 0)
                        <span class="ml-1 rounded-full bg-violet-50 px-2 py-0.5 font-semibold text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                            ★ {{ $sales['freeCount'] }} on free access
                        </span>
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.settings.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-600 dark:text-brand-400">Edit pricing &rarr;</a>
        </div>

        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div class="animate-slide-up rounded-card border border-emerald-200 bg-emerald-50/60 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Active subscribers</p>
                <p class="mt-2 text-3xl font-extrabold tabular-nums text-emerald-700 dark:text-emerald-300">{{ number_format($sales['activeCount']) }}</p>
                <p class="mt-1 text-xs text-emerald-700/70 dark:text-emerald-400/70">
                    MRR ₱{{ number_format($sales['mrr'] / 100, 2) }}
                </p>
            </div>

            <div class="stagger-1 animate-slide-up rounded-card border border-brand-200 bg-brand-50/60 p-5 dark:border-brand-500/30 dark:bg-brand-500/10">
                <p class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">On trial</p>
                <p class="mt-2 text-3xl font-extrabold tabular-nums text-brand-600 dark:text-brand-300">{{ number_format($sales['trialCount']) }}</p>
                <p class="mt-1 text-xs text-brand-600/70 dark:text-brand-400/70">Not yet paying</p>
            </div>

            <div class="stagger-2 animate-slide-up rounded-card border p-5 {{ $sales['lateDueCount'] > 0 ? 'border-red-200 bg-red-50/60 dark:border-red-500/30 dark:bg-red-500/10' : 'border-slate-200/80 bg-white dark:border-white/10 dark:bg-navy-800' }}">
                <p class="text-xs font-bold uppercase tracking-wider {{ $sales['lateDueCount'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-500 dark:text-slate-400' }}">Late due</p>
                <p class="mt-2 text-3xl font-extrabold tabular-nums {{ $sales['lateDueCount'] > 0 ? 'text-red-600 dark:text-red-300' : 'text-slate-900 dark:text-white' }}">{{ number_format($sales['lateDueCount']) }}</p>
                <p class="mt-1 text-xs {{ $sales['lateDueCount'] > 0 ? 'text-red-600/70 dark:text-red-400/70' : 'text-slate-500 dark:text-slate-400' }}">Lapsed — chase these</p>
            </div>

            <div class="stagger-3 animate-slide-up rounded-card border border-slate-200/80 bg-white p-5 dark:border-white/10 dark:bg-navy-800">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Revenue this month</p>
                <p class="mt-2 text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white">₱{{ number_format($sales['revenueThisMonth'] / 100) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">₱{{ number_format($sales['revenueAllTime'] / 100) }} all time</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Late due --}}
            <x-card :padding="false">
                <x-slot:title>Late due</x-slot:title>
                <div class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($sales['lateDueList'] as $u)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $u->name }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $u->email }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-red-600 dark:text-red-400">
                                @if ($u->subscribed_until)
                                    lapsed {{ $u->subscribed_until->diffForHumans(['parts' => 1]) }}
                                @elseif ($u->trial_ends_at)
                                    trial ended {{ $u->trial_ends_at->diffForHumans(['parts' => 1]) }}
                                @endif
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-xs text-slate-500 dark:text-slate-400">Nobody is overdue.</p>
                    @endforelse
                </div>
            </x-card>

            {{-- Renewals due within 7 days --}}
            <x-card :padding="false">
                <x-slot:title>Expiring within 7 days</x-slot:title>
                <div class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($sales['expiringSoon'] as $u)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $u->name }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $u->email }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-amber-600 dark:text-amber-400">
                                {{ $u->subscribed_until->format('M d') }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-xs text-slate-500 dark:text-slate-400">No renewals due this week.</p>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>

    {{-- ═══════════ SCHOOLS ═══════════ --}}
    <div class="space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">Schools</h2>
            <a href="{{ route('admin.school-years.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-600 dark:text-brand-400">Manage school years &rarr;</a>
        </div>

        <x-card :padding="false">
            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($schools as $school)
                    <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $school->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $school->users_count }} {{ Str::plural('teacher', $school->users_count) }}
                                @unless ($school->is_active) · <span class="text-red-500">inactive</span> @endunless
                            </p>
                        </div>
                        <span class="badge shrink-0 self-start bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 sm:self-auto">
                            SY {{ $school->activeSchoolYear?->name ?? ($globalYear?->name ?? '—') }}
                            @unless ($school->active_school_year_id) <span class="opacity-60">(global)</span> @endunless
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-xs text-slate-500 dark:text-slate-400">No schools yet — add one under Schools.</p>
                @endforelse
            </div>
        </x-card>
    </div>
</x-admin-layout>
