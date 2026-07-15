<x-admin-layout title="Audit Logs">
    <x-slot name="breadcrumbs">Admin / Audit Logs</x-slot>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-400">Action</label>
            <select name="action" onchange="this.form.submit()" class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All actions</option>
                @foreach ($actions as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ ucfirst(str_replace('_',' ',$a)) }}</option>@endforeach
            </select>
        </div>
        <div><label class="block text-xs text-gray-400">From</label><input type="date" name="from" value="{{ request('from') }}" class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm"></div>
        <div><label class="block text-xs text-gray-400">To</label><input type="date" name="to" value="{{ request('to') }}" class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm"></div>
        <button class="rounded-lg bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm">Filter</button>
        @if (request()->hasAny(['action','from','to']))<a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>@endif
    </form>

    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead><tr class="text-left text-xs uppercase tracking-wide text-gray-400">
                    <th class="px-3 py-2">When</th><th class="px-3 py-2">User</th><th class="px-3 py-2">Action</th><th class="px-3 py-2">Description</th><th class="px-3 py-2">IP</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                            <td class="px-3 py-2">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-3 py-2"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] capitalize dark:bg-gray-700">{{ str_replace('_',' ',$log->action) }}</span></td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $log->description }}</td>
                            <td class="px-3 py-2 font-mono text-[11px] text-gray-400">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-10 text-center text-gray-400">No audit entries match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </x-card>
</x-admin-layout>
