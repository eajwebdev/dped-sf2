@props([
    'action',
    'title' => 'Delete this record?',
    'message' => 'This action cannot be undone.',
])

{{-- Alias of <x-confirm-delete>. SweetAlert confirmation is handled once, globally,
     by partials/confirm-delete-script (included in the layout). --}}
<form method="POST" action="{{ $action }}" class="js-confirm-delete inline"
      data-title="{{ $title }}" data-message="{{ $message }}" {{ $attributes }}>
    @csrf
    @method('DELETE')
    <button type="submit" title="Delete"
            class="inline-flex items-center justify-center p-2 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
    </button>
</form>
