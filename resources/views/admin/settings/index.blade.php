<x-admin-layout title="Settings">
    <div class="mx-auto max-w-2xl space-y-8">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Settings</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">System configuration, one module per card.</p>
        </div>

        {{-- ═══ Module: Pricing ═══ --}}
        <form method="POST" action="{{ route('admin.settings.update') }}"
              x-data="{ price: {{ old('price', $price) }}, discount: {{ old('discount', $discount) }} }">
            @csrf
            @method('PUT')
            <input type="hidden" name="module" value="pricing">

            <x-card>
                <x-slot:title>Pricing</x-slot:title>
                <div class="space-y-5">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        What each teacher pays per month. Changes apply to new charges — already-paid periods are untouched.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="label">Monthly price (₱) <span class="text-brand-500">*</span></label>
                            <input id="price" name="price" type="number" step="0.01" min="1" max="100000" required
                                   x-model.number="price" class="input @error('price') input-error @enderror">
                            @error('price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="discount" class="label">Discount (%)</label>
                            <input id="discount" name="discount" type="number" min="0" max="100" required
                                   x-model.number="discount" class="input @error('discount') input-error @enderror">
                            @error('discount')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="rounded-card border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-navy-900/50">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Teachers will be charged</p>
                        <p class="mt-1 flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white"
                                  x-text="'₱' + (price * (100 - discount) / 100).toFixed(2)"></span>
                            <template x-if="discount > 0">
                                <span class="text-sm text-slate-500 line-through dark:text-slate-400" x-text="'₱' + Number(price).toFixed(2)"></span>
                            </template>
                            <span class="text-sm text-slate-500 dark:text-slate-400">/ month</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Currently live: <strong>₱{{ number_format($effective, 2) }}</strong>/month</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary btn-md">Save pricing</button>
                    </div>
                </div>
            </x-card>
        </form>

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
