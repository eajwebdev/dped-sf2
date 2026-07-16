<x-admin-layout title="Registrations">
    <x-slot name="breadcrumbs">Admin / Registrations</x-slot>

    <div class="space-y-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Teachers who signed up through the public site. Approving one starts their {{ \App\Models\User::TRIAL_DAYS }}-day free trial
            and creates their teacher profile.
        </p>

        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-navy-800 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-navy-800/50">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Contact</th>
                            <th class="px-6 py-4">School</th>
                            <th class="px-6 py-4">Requested</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($pending as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $user->contact_number ?: '—' }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    @if ($user->school)
                                        {{ $user->school->name }}
                                        <span class="block text-xs text-gray-400">ID {{ $user->school->school_id }}</span>
                                    @else
                                        <span class="text-amber-500">No school</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 tabular-nums">{{ $user->created_at->format('M j, Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.registrations.approve', $user) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.registrations.reject', $user) }}"
                                              onsubmit="return confirm('Reject {{ $user->name }}\'s registration?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 dark:border-red-500/40 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No pending registrations</p>
                                <p class="mt-1 text-xs text-gray-500">New teacher sign-ups will appear here for approval.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($pending->hasPages())<div>{{ $pending->links() }}</div>@endif
    </div>
</x-admin-layout>
