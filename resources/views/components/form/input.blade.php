@props(['label', 'name', 'value' => null, 'type' => 'text', 'required' => false, 'hint' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $label }}@if($required)<span class="text-red-500"> *</span>@endif
    </label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
           value="{{ old($name, $value) }}" @if($required) required @endif
           {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm']) }}>
    @if($hint)<p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
