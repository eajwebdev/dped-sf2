<x-admin-layout title="Settings">
    <div class="mx-auto max-w-2xl space-y-8">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Settings</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">System configuration, one module per card.</p>
        </div>

        {{-- ═══ Module: Plan pricing ═══ --}}
        <form method="POST" action="{{ route('admin.settings.update') }}"
              x-data="{ tiers: {
                  @foreach ($tiers as $key => $tier)
                      '{{ $key }}': {
                          price: {{ old('prices.'.$key, $tier['price']) }},
                          discount: {{ old('discounts.'.$key, $tier['discount']) }}
                      },
                  @endforeach
              } }">
            @csrf
            @method('PUT')
            <input type="hidden" name="module" value="pricing">

            <x-card>
                <x-slot:title>Plan pricing</x-slot:title>
                <div class="space-y-5">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Each tier's monthly price and its own promo discount. Saving takes effect immediately on the
                        public landing page and at checkout — already-paid periods are never re-charged or changed.
                    </p>

                    @foreach ($tiers as $key => $tier)
                        <div class="rounded-card border border-slate-200 p-4 dark:border-white/10">
                            <div class="flex items-baseline justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $tier['name'] }}</h3>
                                    <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $tier['tagline'] }}</p>
                                </div>
                                {{-- Live preview of what this tier will actually charge --}}
                                <p class="shrink-0 text-right">
                                    <span class="text-xl font-extrabold text-slate-900 dark:text-white"
                                          x-text="'₱' + (tiers['{{ $key }}'].price * (100 - tiers['{{ $key }}'].discount) / 100).toFixed(2)"></span>
                                    <template x-if="tiers['{{ $key }}'].discount > 0">
                                        <span class="ml-1 text-xs text-slate-400 line-through"
                                              x-text="'₱' + Number(tiers['{{ $key }}'].price).toFixed(2)"></span>
                                    </template>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">/mo</span>
                                </p>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div>
                                    <label for="price_{{ $key }}" class="label">Monthly price (₱) <span class="text-brand-500">*</span></label>
                                    <input id="price_{{ $key }}" name="prices[{{ $key }}]" type="number" step="0.01" min="1" max="100000" required
                                           x-model.number="tiers['{{ $key }}'].price"
                                           class="input @error('prices.'.$key) input-error @enderror">
                                    @error('prices.'.$key)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="discount_{{ $key }}" class="label">Discount (%)</label>
                                    <input id="discount_{{ $key }}" name="discounts[{{ $key }}]" type="number" min="0" max="100" required
                                           x-model.number="tiers['{{ $key }}'].discount"
                                           class="input @error('discounts.'.$key) input-error @enderror">
                                    @error('discounts.'.$key)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                                Currently live: <strong>₱{{ number_format($tier['effective'], 2) }}</strong>/month
                                @if ($tier['discount'] > 0)
                                    <span class="text-emerald-600 dark:text-emerald-400">({{ $tier['discount'] }}% off ₱{{ number_format($tier['price'], 2) }})</span>
                                @endif
                            </p>
                        </div>
                    @endforeach

                    <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                        The multi-month advance discount ({{ \App\Support\SubscriptionPlans::DISCOUNT_PER_EXTRA_MONTH }}% per extra
                        month, up to {{ \App\Support\SubscriptionPlans::MAX_DISCOUNT_PERCENT }}%) stacks on top of the promo above.
                    </p>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary btn-md">Save pricing</button>
                    </div>
                </div>
            </x-card>
        </form>

        {{-- ═══ Go-live checklist ═══ --}}
        @php $blocking = collect($readiness)->reject(fn ($c) => $c['ok']); @endphp
        <x-card>
            <x-slot:title>Payment readiness</x-slot:title>
            <x-slot:actions>
                @if ($blocking->isEmpty())
                    <span class="badge bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Ready</span>
                @else
                    <span class="badge bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">{{ $blocking->count() }} to fix</span>
                @endif
            </x-slot:actions>

            <div class="space-y-3">
                @if ($liveMode)
                    <div class="rounded-card border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        Live mode — checkouts on this site charge real money.
                    </div>
                @endif

                @foreach ($readiness as $label => $check)
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $check['ok'] ? 'bg-emerald-100 dark:bg-emerald-500/15' : 'bg-amber-100 dark:bg-amber-500/15' }}">
                            @if ($check['ok'])
                                <svg class="h-3 w-3 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            @else
                                <svg class="h-3 w-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $label }}</p>
                            <p class="break-all text-xs text-slate-500 dark:text-slate-400">{{ $check['detail'] }}</p>
                        </div>
                    </div>
                @endforeach

                <p class="border-t border-slate-100 pt-3 text-[11px] leading-relaxed text-slate-500 dark:border-white/10 dark:text-slate-400">
                    Payments are confirmed three ways — the return page, the webhook, and a scheduled
                    <code class="rounded bg-slate-100 px-1 dark:bg-white/10">subscriptions:reconcile</code> sweep — so a
                    teacher who pays always gets access. The sweep needs Laravel's scheduler running
                    (<code class="rounded bg-slate-100 px-1 dark:bg-white/10">* * * * * php artisan schedule:run</code>).
                </p>
            </div>
        </x-card>

        {{-- ═══ Module: Payments (PayMongo) ═══ --}}
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="module" value="payments">

            <x-card>
                <x-slot:title>Payments — PayMongo</x-slot:title>
                <div class="space-y-5">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Keys from <a href="https://dashboard.paymongo.com/developers" target="_blank" rel="noopener" class="font-semibold text-brand-500 hover:text-brand-600">dashboard.paymongo.com/developers</a>.
                        Leave a field blank to keep its current value — saved keys are shown masked and never sent back in full.
                    </p>

                    <div>
                        <label for="paymongo_secret_key" class="label">Secret key</label>
                        <input id="paymongo_secret_key" name="paymongo_secret_key" type="password" autocomplete="new-password"
                               placeholder="{{ $secretHint ? 'Enter a new key to replace the saved one' : 'sk_live_…' }}" class="input font-mono text-xs">
                        @if ($secretHint)
                            <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                ✓ Saved: <span class="font-mono">{{ $secretHint }}</span>
                                <span class="font-normal text-slate-500 dark:text-slate-400">— the box stays empty for security; blank keeps it.</span>
                            </p>
                        @else
                            <p class="mt-1 text-xs font-semibold text-amber-600 dark:text-amber-400">Not set</p>
                        @endif
                    </div>
                    <div>
                        <label for="paymongo_public_key" class="label">Public key</label>
                        {{-- Publishable by definition, so it can prefill in full --}}
                        <input id="paymongo_public_key" name="paymongo_public_key" type="text" autocomplete="off"
                               value="{{ $publicFull }}" placeholder="pk_live_…" class="input font-mono text-xs">
                        @unless ($publicFull)<p class="mt-1 text-xs font-semibold text-amber-600 dark:text-amber-400">Not set</p>@endunless
                    </div>
                    <div>
                        <label for="paymongo_webhook_secret" class="label">Webhook secret</label>
                        <input id="paymongo_webhook_secret" name="paymongo_webhook_secret" type="password" autocomplete="new-password"
                               placeholder="{{ $webhookHint ? 'Enter a new secret to replace the saved one' : 'whsk_…' }}" class="input font-mono text-xs">
                        @if ($webhookHint)
                            <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                ✓ Saved: <span class="font-mono">{{ $webhookHint }}</span>
                                <span class="font-normal text-slate-500 dark:text-slate-400">— blank keeps it.</span>
                            </p>
                        @else
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Optional — when unset, webhook signatures are not verified (sandbox convenience).</p>
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="badge {{ $secretHint ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' }}">
                            {{ $secretHint ? 'Gateway configured' : 'Gateway not configured — checkout disabled' }}
                        </span>
                        <button type="submit" class="btn-primary btn-md">Save payment keys</button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>
</x-admin-layout>
