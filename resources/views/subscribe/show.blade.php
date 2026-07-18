@php
    use App\Support\SubscriptionPlans;

    $user = auth()->user();
    $state = $user->subscriptionState();
@endphp
<x-app-shell title="Subscription">
    <div class="mx-auto max-w-5xl space-y-6">

        {{-- ═══ Current status ═══ --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-navy-800">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Your account</h2>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                @if ($state === 'managed')
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Your access is managed by your administrator.</span>
                @elseif ($state === 'active')
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Subscribed</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        until {{ $user->subscribed_until->format('F j, Y') }}
                        @if ($user->subscription_plan)
                            · <span class="font-semibold">{{ SubscriptionPlans::find($user->subscription_plan)['name'] }}</span> plan
                        @endif
                    </span>
                @elseif ($state === 'trial')
                    <span class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-3 py-1.5 text-sm font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"><span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>Free trial</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">ends {{ $user->trial_ends_at->format('F j, Y') }} ({{ $user->trial_ends_at->diffForHumans() }})</span>
                @else
                    <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1.5 text-sm font-semibold text-red-700 dark:bg-red-500/15 dark:text-red-300"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Expired</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Subscribe to continue using {{ config('app.name') }}.</span>
                @endif
            </div>
        </div>

        @if (! $configured)
            <div class="rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
                Online payment isn’t configured yet. Please contact your administrator to enable subscriptions.
            </div>
        @endif

        {{-- ═══ Plan + months picker ═══ --}}
        <form method="POST" action="{{ route('subscribe.checkout') }}"
              x-data="{
                  plan: @js($currentPlan),
                  months: 1,
                  quotes: @js($quotes),
                  get q() { return this.quotes[this.plan][this.months] },
                  peso(centavos) { return '₱' + Math.round(centavos / 100).toLocaleString('en-PH') },
              }">
            @csrf
            {{-- Submitted values mirror the Alpine state; the server re-quotes anyway. --}}
            <input type="hidden" name="plan" :value="plan">
            <input type="hidden" name="months" :value="months">

            {{-- Plan cards --}}
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($plans as $key => $meta)
                    <label class="relative cursor-pointer">
                        <input type="radio" value="{{ $key }}" x-model="plan" class="sr-only">
                        <div class="h-full rounded-2xl border-2 bg-white p-5 transition-all dark:bg-navy-800"
                             :class="plan === '{{ $key }}'
                                 ? 'border-brand-500 shadow-lg shadow-brand-500/15 dark:border-brand-400'
                                 : 'border-gray-200 hover:border-gray-300 dark:border-white/10 dark:hover:border-white/20'">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $meta['name'] }}</h3>
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
                                      :class="plan === '{{ $key }}' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-white/20'">
                                    <svg x-show="plan === '{{ $key }}'" x-cloak class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                            </div>
                            <div class="mt-3 flex items-end gap-1">
                                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">₱{{ number_format(SubscriptionPlans::monthlyPrice($key) / 100, 0) }}</span>
                                <span class="mb-1 text-xs text-gray-500 dark:text-gray-400">/ month</span>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $meta['tagline'] }}</p>
                            <ul class="mt-4 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                @foreach ($meta['perks'] as $perk)
                                    <li class="flex items-start gap-2">
                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ $perk }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Months + total --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-navy-800">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">How many months?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Save {{ $perMonthDiscount }}% for every extra month paid in advance, up to {{ $maxDiscount }}%.
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-12">
                    @for ($m = 1; $m <= $maxMonths; $m++)
                        <button type="button" @click="months = {{ $m }}"
                                class="relative cursor-pointer rounded-xl border-2 py-2 text-sm font-bold transition-all"
                                :class="months === {{ $m }}
                                    ? 'border-brand-500 bg-brand-500 text-white shadow-glow-pink-sm'
                                    : 'border-gray-200 text-gray-600 hover:border-brand-300 dark:border-white/10 dark:text-gray-300 dark:hover:border-brand-500/40'">
                            {{ $m }}
                            @if (SubscriptionPlans::discountFor($m) > 0)
                                <span class="absolute -right-1 -top-1.5 rounded-full bg-emerald-500 px-1 text-[9px] font-bold leading-4 text-white">
                                    -{{ SubscriptionPlans::discountFor($m) }}%
                                </span>
                            @endif
                        </button>
                    @endfor
                </div>

                {{-- Live quote --}}
                <div class="mt-6 space-y-2 border-t border-gray-100 pt-5 text-sm dark:border-white/10">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>
                            <span x-text="q.months"></span> ×
                            <span x-text="peso(q.monthly)"></span> / month
                        </span>
                        <span x-text="peso(q.subtotal)"></span>
                    </div>

                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400" x-show="q.discount > 0" x-cloak>
                        <span>Advance-payment discount (<span x-text="q.discount"></span>%)</span>
                        <span>−<span x-text="peso(q.saved)"></span></span>
                    </div>

                    <div class="flex items-baseline justify-between border-t border-gray-100 pt-3 dark:border-white/10">
                        <span class="font-bold text-gray-900 dark:text-white">Total due today</span>
                        <span class="text-2xl font-extrabold text-gray-900 dark:text-white" x-text="peso(q.total)"></span>
                    </div>

                    <p class="text-xs text-gray-400">
                        Adds <span x-text="q.months"></span> <span x-text="q.months === 1 ? 'month' : 'months'"></span>,
                        stacked onto any time you have left.
                    </p>
                </div>

                @if ($configured)
                    <button type="submit" class="mt-6 w-full cursor-pointer rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/30 transition hover:from-brand-700 hover:to-brand-600">
                        Pay <span x-text="peso(q.total)"></span> with card / GCash / Maya
                    </button>
                    <p class="mt-3 text-center text-xs text-gray-400">Secured by PayMongo. You’ll return here after paying.</p>
                @else
                    <button type="button" disabled class="mt-6 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-50 px-6 py-3.5 text-sm font-semibold text-gray-400 dark:border-white/10 dark:bg-white/5">
                        Online payment unavailable
                    </button>
                @endif
            </div>
        </form>
    </div>
</x-app-shell>
