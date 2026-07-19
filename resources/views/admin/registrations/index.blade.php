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
                            <th class="px-6 py-4">School ID</th>
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
                                {{-- Identity check: the admin confirms this ID belongs to their school before approving --}}
                                <td class="px-6 py-4">
                                    @if ($user->school_id_document_path)
                                        <div x-data="{ open: false }" class="flex items-center gap-3">
                                            <button type="button" @click="open = true"
                                                    class="group relative h-12 w-20 shrink-0 overflow-hidden rounded-lg border border-gray-200 dark:border-white/15 bg-gray-100 dark:bg-navy-900"
                                                    title="View full size">
                                                <img src="{{ route('admin.registrations.school-id', $user) }}" alt="School ID"
                                                     class="h-full w-full object-cover transition-transform group-hover:scale-105" loading="lazy">
                                                <span class="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/40">
                                                    <svg class="h-4 w-4 text-white opacity-0 transition-opacity group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m2.2-5.3a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/></svg>
                                                </span>
                                            </button>
                                            <span class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ $user->school_id_number ?: '—' }}</span>

                                            {{-- Lightbox --}}
                                            <div x-show="open" x-cloak @keydown.escape.window="open = false"
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
                                                 x-transition.opacity>
                                                <div @click.outside="open = false" class="max-h-full w-full max-w-3xl overflow-auto rounded-2xl bg-white dark:bg-navy-800 p-4 shadow-2xl">
                                                    <div class="mb-3 flex items-center justify-between gap-4">
                                                        <div>
                                                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ $user->school?->name }} · ID {{ $user->school_id_number ?: '—' }}
                                                            </p>
                                                        </div>
                                                        <button type="button" @click="open = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10" aria-label="Close">
                                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                    <img src="{{ route('admin.registrations.school-id', $user) }}" alt="School ID for {{ $user->name }}"
                                                         class="mx-auto max-h-[70vh] rounded-lg object-contain">
                                                    <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                                                        Check the name and school match this application before approving.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.34 3.94L2.7 16.13c-.87 1.5.21 3.37 1.94 3.37h14.72c1.73 0 2.81-1.87 1.94-3.37L13.66 3.94c-.87-1.5-3.03-1.5-3.9 0z"/></svg>
                                            No ID on file
                                        </span>
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
                            <tr><td colspan="7" class="px-6 py-12 text-center">
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
