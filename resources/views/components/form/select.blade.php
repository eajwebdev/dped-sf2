@props(['label', 'name', 'value' => null, 'required' => false, 'placeholder' => null, 'hint' => null])

<div>
    <label for="{{ $name }}" class="label">
        {{ $label }}@if($required)<span class="text-brand-500"> *</span>@endif
    </label>
    <select name="{{ $name }}" id="{{ $name }}" @if($required) required @endif
            {{ $attributes->merge(['class' => 'input cursor-pointer' . ($errors->has($name) ? ' input-error' : '')]) }}>
        @if($placeholder)<option value="">{{ $placeholder }}</option>@endif
        {{ $slot }}
    </select>
    @if($hint)<p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-500">{{ $message }}</p>@enderror
</div>
