@props(['title' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow']) }}>
    @if ($title || $actions)
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800/50">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
            @if ($actions)<div class="flex items-center gap-2">{{ $actions }}</div>@endif
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
