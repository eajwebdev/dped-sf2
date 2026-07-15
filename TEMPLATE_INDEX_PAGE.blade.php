{{--
Template for modernizing index pages
Replace ITEM with your actual model name (GradeLevel, Section, Teacher, etc.)
Replace items with your actual collection variable
Update routes, columns, and form includes
--}}

<x-admin-layout title="Items">
    <x-slot name="breadcrumbs">Admin / Items</x-slot>

    <div x-data="{
        createOpen: false,
        editOpen: false,
        editItem: null
    }" class="space-y-6">

        {{-- Toolbar --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative">
                    <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Search…"
                           class="w-full sm:w-64 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm pl-4 pr-10 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Search</button>
            </form>

            <button @click="createOpen = true" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                New Item
            </button>
        </div>

        {{-- Create Modal --}}
        <div x-show="createOpen" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape="createOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="createOpen = false">
            <div x-show="createOpen" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-gray-800 shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-5 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-800 dark:to-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New Item</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fill in the details below to create a new item</p>
                    </div>
                    <button @click="createOpen = false" class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    @include('admin.items.form', ['item' => null])
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="editOpen" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape="editOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="editOpen = false">
            <div x-show="editOpen" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-gray-800 shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Item</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update item details below</p>
                    </div>
                    <button @click="editOpen = false" class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <template x-if="editItem">
                        @include('admin.items.form', ['item' => null])
                    </template>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700/50">
                        @forelse ($items as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $item->name }}</td>
                                <td class="px-6 py-4">
                                    @if ($item->is_active ?? true)
                                        <x-badge variant="green">Active</x-badge>
                                    @else
                                        <x-badge variant="gray">Inactive</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editItem = @json($item); editOpen = true" class="inline-flex items-center justify-center p-2.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors" title="Edit">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <x-delete-confirm-btn :action="route('admin.items.destroy', $item)" title="Delete Item" message="Are you sure? This action cannot be undone." />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No items found</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Create your first item to get started</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($items->hasPages())
            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
