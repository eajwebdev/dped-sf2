@props([
    'createTitle' => 'Add New',
    'editTitle' => 'Edit',
    'createSubtitle' => 'Fill in the details below',
    'editSubtitle' => 'Update the details below',
    'submitCreate' => 'Create',
    'submitEdit' => 'Save Changes',
    'maxWidth' => 'max-w-2xl',
])

{{-- Lives inside an x-data="resourceModal({...})" scope. Renders one modal that
     serves both create and edit, driven by the shared Alpine component. --}}
<div x-show="open" x-cloak
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="close()">
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="w-full {{ $maxWidth }} max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-navy-800 shadow-2xl">
        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-6 py-5 bg-gradient-to-r from-brand-50 to-navy-50 dark:from-navy-800 dark:to-navy-700">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="isEdit ? @js($editTitle) : @js($createTitle)"></h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="isEdit ? @js($editSubtitle) : @js($createSubtitle)"></p>
            </div>
            <button type="button" @click="close()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-navy-700 rounded-lg transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form :action="action" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_edit_id" :value="editingId">

            {{ $slot }}

            <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-white/10 pt-5">
                <button type="button" @click="close()" class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-navy-700 transition-colors">Cancel</button>
                <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-2.5 text-sm font-bold text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all"
                        x-text="isEdit ? @js($submitEdit) : @js($submitCreate)"></button>
            </div>
        </form>
    </div>
</div>
