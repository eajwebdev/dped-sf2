@props(['label', 'name', 'checked' => false, 'hint' => null])

<label class="flex items-start gap-3 cursor-pointer">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))
           {{ $attributes->merge(['class' => 'mt-0.5 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500']) }}>
    <span>
        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
        @if($hint)<span class="block text-xs text-gray-400">{{ $hint }}</span>@endif
    </span>
</label>
