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
                @if ($state === 'free')
                    {{-- Owner-granted comp: overrides billing entirely, so this
                         account must never be told to subscribe. --}}
                    <span class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-3 py-1.5 text-sm font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                        Unlimited access
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Your administrator has granted you full access to {{ config('app.name') }} — every School Form
                        is unlocked and there is nothing to pay.
                    </span>
                @elseif ($state === 'managed')
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

        {{-- ═══ Active subscription: this page becomes an upgrade screen ═══ --}}
        @if ($subscribed)
            <div class="mb-5 rounded-2xl border border-brand-300/60 bg-brand-50/60 p-5 dark:border-brand-400/25 dark:bg-brand-500/10">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500/15 text-brand-600 dark:text-brand-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                            You're on {{ $plans[$currentPlan]['name'] }} until {{ $subscribedUntil->format('F j, Y') }}
                            <span class="font-normal text-gray-500 dark:text-gray-400">
                                ({{ $remainingMonths }} {{ Str::plural('month', $remainingMonths) }} left)
                            </span>
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-gray-300">
                            That time is already paid for, so there's nothing to buy again. You can move up a tier
                            whenever you like and pay only the difference for the months you have left —
                            your end date stays the same.
                            @unless ($canRenew)
                                Renewal opens {{ $renewalWindowDays }} days before {{ $subscribedUntil->format('M j') }}.
                            @endunless
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- A comped account can still see the tiers, but must not be nudged to
             buy one — the plans below are informational until the comp ends. --}}
        @if ($state === 'free')
            <div class="rounded-2xl border border-brand-300/60 bg-brand-50/60 px-5 py-4 text-sm text-brand-800 dark:border-brand-400/25 dark:bg-brand-500/10 dark:text-brand-200">
                You don't need to buy any of these. The plans below are shown for reference only — if your
                unlimited access is ever removed, you can subscribe from this page then.
            </div>
        @endif

        {{-- ═══ Plan + months picker ═══ --}}
        <form method="POST" action="{{ route('subscribe.checkout') }}"
              x-data="{
                  subscribed: @js($subscribed),
                  currentPlan: @js($currentPlan),
                  canRenew: @js($canRenew),
                  remainingMonths: @js($remainingMonths),
                  ranks: @js(array_flip(array_values(\App\Support\SubscriptionPlans::keys()))),
                  upgrades: @js($upgradeQuotes),
                  quotes: @js($quotes),

                  plan: @js($subscribed ? ($upgradeQuotes ? array_key_first($upgradeQuotes) : $currentPlan) : $currentPlan),
                  months: 1,

                  /* Upgrading whenever an active subscriber picks a different tier. */
                  get upgrading() { return this.subscribed && this.plan !== this.currentPlan },

                  /* A tier is offered if it is an upgrade, or a renewal that is due. */
                  selectable(key) {
                      if (! this.subscribed) return true;
                      if (key === this.currentPlan) return this.canRenew;
                      return this.ranks[key] > this.ranks[this.currentPlan];
                  },

                  get q() {
                      if (this.upgrading) {
                          const u = this.upgrades[this.plan];
                          return { months: u.months, monthly: u.monthly_difference, subtotal: u.subtotal,
                                   discount: 0, saved: 0, total: u.total };
                      }
                      return this.quotes[this.plan][this.months];
                  },
                  peso(centavos) { return '₱' + Math.round(centavos / 100).toLocaleString('en-PH') },
              }">
            @csrf
            {{-- Submitted values mirror the Alpine state; the server re-quotes anyway. --}}
            <input type="hidden" name="plan" :value="plan">
            <input type="hidden" name="months" :value="upgrading ? remainingMonths : months">

            {{-- Plan cards --}}
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($plans as $key => $meta)
                    <label class="relative" :class="selectable('{{ $key }}') ? 'cursor-pointer' : 'cursor-not-allowed'">
                        <input type="radio" value="{{ $key }}" x-model="plan" class="sr-only"
                               :disabled="! selectable('{{ $key }}')">
                        <div class="h-full rounded-2xl border-2 bg-white p-5 transition-all dark:bg-navy-800"
                             :class="! selectable('{{ $key }}')
                                 ? 'border-gray-200 opacity-55 dark:border-white/10'
                                 : (plan === '{{ $key }}'
                                     ? 'border-brand-500 shadow-lg shadow-brand-500/15 dark:border-brand-400'
                                     : 'border-gray-200 hover:border-gray-300 dark:border-white/10 dark:hover:border-white/20')">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $meta['name'] }}</h3>

                                {{-- The tier already owned is marked rather than offered --}}
                                <template x-if="subscribed && '{{ $key }}' === currentPlan">
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                        Current
                                    </span>
                                </template>
                                <template x-if="! (subscribed && '{{ $key }}' === currentPlan)">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
                                          :class="plan === '{{ $key }}' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-white/20'">
                                        <svg x-show="plan === '{{ $key }}'" x-cloak class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </span>
                                </template>
                            </div>
                            <div class="mt-3 flex items-end gap-1">
                                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">₱{{ number_format(SubscriptionPlans::monthlyPrice($key) / 100, 0) }}</span>
                                <span class="mb-1 text-xs text-gray-500 dark:text-gray-400">/ month</span>
                            </div>

                            {{-- What this tier actually costs an existing subscriber --}}
                            @if (isset($upgradeQuotes[$key]))
                                <p class="mt-2 rounded-lg bg-brand-50 px-2.5 py-1.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                    Upgrade for ₱{{ number_format($upgradeQuotes[$key]['total'] / 100, 0) }}
                                    <span class="font-normal">
                                        (+₱{{ number_format($upgradeQuotes[$key]['monthly_difference'] / 100, 0) }}/mo ×
                                        {{ $remainingMonths }} {{ Str::plural('month', $remainingMonths) }})
                                    </span>
                                </p>
                            @elseif ($subscribed && $key !== $currentPlan)
                                <p class="mt-2 text-xs italic text-gray-400">Not available while you're on a higher plan.</p>
                            @endif
                            <p class="mt-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $meta['tagline'] }}</p>
                            <ul class="mt-4 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                @foreach ($meta['perks'] as $perk)
                                    <li class="flex items-start gap-2 {{ $perk['live'] ? '' : 'opacity-60' }}">
                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 {{ $perk['live'] ? 'text-brand-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        <span>
                                            {{ $perk['label'] }}
                                            @unless ($perk['live'])
                                                <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-gray-400 dark:bg-white/10">On release</span>
                                            @endunless
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Months + total --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-navy-800">
                {{-- An upgrade covers the months already paid for, so there is nothing to choose --}}
                <div x-show="upgrading" x-cloak class="flex items-start gap-2.5 rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/5">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-300">
                        Covers the <span class="font-semibold" x-text="remainingMonths"></span>
                        <span x-text="remainingMonths === 1 ? 'month' : 'months'"></span> left on your subscription.
                        Your access still ends {{ $subscribedUntil?->format('F j, Y') }} — upgrading changes the plan, not the date.
                    </p>
                </div>

                <div x-show="! upgrading" class="flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">How many months?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Save {{ $perMonthDiscount }}% for every extra month paid in advance, up to {{ $maxDiscount }}%.
                    </p>
                </div>

                <div x-show="! upgrading" class="mt-4 grid grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-12">
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
                        <span x-show="! upgrading">
                            <span x-text="q.months"></span> ×
                            <span x-text="peso(q.monthly)"></span> / month
                        </span>
                        <span x-show="upgrading" x-cloak>
                            <span x-text="q.months"></span> ×
                            <span x-text="peso(q.monthly)"></span> / month difference
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

                    <p class="text-xs text-gray-400" x-show="! upgrading">
                        Adds <span x-text="q.months"></span> <span x-text="q.months === 1 ? 'month' : 'months'"></span>,
                        stacked onto any time you have left.
                    </p>
                    <p class="text-xs text-gray-400" x-show="upgrading" x-cloak>
                        You already paid for these months at the lower rate, so this is the top-up only.
                    </p>
                </div>

                @if ($configured)
                    @php $noUpgradeAvailable = $subscribed && empty($upgradeQuotes) && ! $canRenew; @endphp

                    @if ($noUpgradeAvailable)
                        {{-- Top tier, mid-term: genuinely nothing to buy --}}
                        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-center dark:border-emerald-500/25 dark:bg-emerald-500/10">
                            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                You're on our highest plan — nothing to pay right now.
                            </p>
                            <p class="mt-0.5 text-xs text-emerald-700/80 dark:text-emerald-400/80">
                                Renewal opens {{ $renewalWindowDays }} days before {{ $subscribedUntil->format('M j, Y') }}.
                            </p>
                        </div>
                    @else
                        <button type="submit"
                                :disabled="subscribed && ! selectable(plan)"
                                class="mt-6 w-full cursor-pointer rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/30 transition hover:from-brand-700 hover:to-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-show="! upgrading">Pay <span x-text="peso(q.total)"></span> with card / GCash / Maya</span>
                            <span x-show="upgrading" x-cloak>Upgrade for <span x-text="peso(q.total)"></span></span>
                        </button>
                        <p class="mt-3 text-center text-xs text-gray-400">Secured by PayMongo. You’ll return here after paying.</p>
                    @endif
                @else
                    <button type="button" disabled class="mt-6 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-50 px-6 py-3.5 text-sm font-semibold text-gray-400 dark:border-white/10 dark:bg-white/5">
                        Online payment unavailable
                    </button>
                @endif
            </div>
        </form>
    </div>
</x-app-shell>
