@props(['label', 'name', 'value' => null, 'type' => 'text', 'required' => false, 'hint' => null])

<div @if ($type === 'password') x-data="{ reveal: false }" @endif>
    <label for="{{ $name }}" class="label">
        {{ $label }}@if($required)<span class="text-brand-500"> *</span>@endif
    </label>
    <div class="relative">
        <input @if ($type === 'password') :type="reveal ? 'text' : 'password'" @else type="{{ $type }}" @endif
               name="{{ $name }}" id="{{ $name }}"
               value="{{ old($name, $value) }}" @if($required) required @endif
               {{ $attributes->merge(['class' => 'input' . ($errors->has($name) ? ' input-error' : '') . ($type === 'password' ? ' pr-11' : '')]) }}>
        @if ($type === 'password')
            <button type="button" @click="reveal = !reveal" tabindex="-1"
                    class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200"
                    :aria-label="reveal ? 'Hide password' : 'Show password'">
                <svg x-show="!reveal" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                <svg x-show="reveal" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
            </button>
        @endif
    </div>
    @if($hint)<p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-500">{{ $message }}</p>@enderror
</div>
