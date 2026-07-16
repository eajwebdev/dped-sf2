@php
    $user = auth()->user();
    $state = $user->subscriptionState();
@endphp
<x-app-shell title="Subscription">
    <div class="mx-auto max-w-xl space-y-6">
        {{-- Current status --}}
        <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 p-6 shadow-sm">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Your account</h2>
            <div class="mt-3 flex items-center gap-3">
                @if ($state === 'managed')
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1.5 text-sm font-semibold text-emerald-700 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Your access is managed by your administrator.</span>
                @elseif ($state === 'active')
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1.5 text-sm font-semibold text-emerald-700 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Subscribed</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">until {{ $user->subscribed_until->format('F j, Y') }}</span>
                @elseif ($state === 'trial')
                    <span class="inline-flex items-center gap-2 rounded-full bg-brand-100 dark:bg-brand-500/15 px-3 py-1.5 text-sm font-semibold text-brand-700 dark:text-brand-300"><span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>Free trial</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">ends {{ $user->trial_ends_at->format('F j, Y') }} ({{ $user->trial_ends_at->diffForHumans() }})</span>
                @else
                    <span class="inline-flex items-center gap-2 rounded-full bg-red-100 dark:bg-red-500/15 px-3 py-1.5 text-sm font-semibold text-red-700 dark:text-red-300"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Expired</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Subscribe to continue using {{ config('app.name') }}.</span>
                @endif
            </div>
        </div>

        {{-- Plan / pay --}}
        <div class="rounded-2xl border border-brand-200 dark:border-brand-500/30 bg-gradient-to-b from-brand-50 to-white dark:from-brand-500/10 dark:to-gray-800 p-8 text-center shadow-sm">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Monthly Subscription</h2>
            <div class="mt-3 flex items-end justify-center gap-1">
                <span class="text-4xl font-extrabold text-gray-900 dark:text-white">₱{{ number_format($price, 0) }}</span>
                <span class="mb-1.5 text-sm text-gray-500 dark:text-gray-400">/ month</span>
            </div>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Adds one month. Stacks onto any time you have left.</p>

            @if ($configured)
                <form method="POST" action="{{ route('subscribe.checkout') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/30 transition hover:from-brand-700 hover:to-brand-600">
                        Pay ₱{{ number_format($price, 0) }} with card / GCash / Maya
                    </button>
                </form>
                <p class="mt-3 text-xs text-gray-400">Secured by PayMongo. You’ll return here after paying.</p>
            @else
                <div class="mt-6 rounded-xl border border-amber-300 dark:border-amber-500/40 bg-amber-50 dark:bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
                    Online payment isn’t configured yet. Please contact your administrator to enable subscriptions.
                </div>
            @endif
        </div>
    </div>
</x-app-shell>
