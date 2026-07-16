@props(['label', 'name', 'accept' => 'image/*', 'required' => false, 'hint' => null, 'preview' => null])

{{-- Image file picker with live preview (used for logos / photos). --}}
<div x-data="{ fileName: '', previewUrl: @js($preview) }">
    <label for="{{ $name }}" class="label">
        {{ $label }}@if($required)<span class="text-brand-500"> *</span>@endif
    </label>

    <div class="flex items-center gap-4">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 dark:border-white/15 dark:bg-navy-900/60">
            <template x-if="previewUrl">
                <img :src="previewUrl" alt="Preview" class="h-full w-full object-contain p-1">
            </template>
            <svg x-show="!previewUrl" class="h-6 w-6 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm10.5-11.25h.008v.008h-.008V9.75Z"/>
            </svg>
        </div>

        <div class="min-w-0 flex-1">
            <label for="{{ $name }}"
                   class="btn-outline btn-sm cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                </svg>
                Choose image
            </label>
            <input type="file" name="{{ $name }}" id="{{ $name }}" accept="{{ $accept }}" @if($required) required @endif
                   class="sr-only"
                   @change="const f = $event.target.files[0];
                            fileName = f ? f.name : '';
                            if (f) previewUrl = URL.createObjectURL(f);">
            <p class="mt-1.5 truncate text-xs text-slate-400 dark:text-slate-500" x-text="fileName || @js($hint ?? 'PNG or JPG, up to 2 MB')"></p>
        </div>
    </div>

    @error($name)<p class="mt-1.5 animate-slide-up text-xs font-medium text-red-500">{{ $message }}</p>@enderror
</div>
