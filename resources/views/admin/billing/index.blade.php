<x-admin-layout title="Billing">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Billing</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Every subscription payment, newest first.</p>
            </div>
            <form method="GET">
                <select name="status" onchange="this.form.submit()" class="input !w-auto text-sm">
                    <option value="">All statuses</option>
                    @foreach (['paid', 'pending', 'failed'] as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <x-card :padding="false">
            <div class="table-scroll overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead>
                        <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-5 py-3">Teacher</th>
                            <th class="px-3 py-3">Amount</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Period</th>
                            <th class="px-3 py-3">Paid at</th>
                            <th class="px-3 py-3">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($payments as $p)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $p->user?->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $p->user?->email }}</p>
                                </td>
                                <td class="px-3 py-3 font-semibold tabular-nums">₱{{ number_format($p->amount / 100, 2) }}</td>
                                <td class="px-3 py-3">
                                    <span class="badge {{ [
                                        'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                        'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                        'failed' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                    ][$p->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($p->status) }}</span>
                                </td>
                                <td class="px-3 py-3 text-xs tabular-nums text-slate-500 dark:text-slate-400">
                                    @if ($p->period_start){{ $p->period_start->format('M d') }} – {{ $p->period_end?->format('M d, Y') }}@else — @endif
                                </td>
                                <td class="px-3 py-3 text-xs tabular-nums text-slate-500 dark:text-slate-400">{{ $p->paid_at?->format('M d, Y H:i') ?? '—' }}</td>
                                <td class="px-3 py-3 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $p->provider_reference ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-xs text-slate-500 dark:text-slate-400">No payments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="border-t border-slate-100 px-5 py-3 dark:border-white/5">{{ $payments->links() }}</div>
            @endif
        </x-card>
    </div>
</x-admin-layout>
