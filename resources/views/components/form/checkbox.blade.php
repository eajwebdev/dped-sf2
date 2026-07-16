@props(['label', 'name', 'checked' => false, 'hint' => null])

<label class="flex cursor-pointer items-start gap-3">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))
           {{ $attributes->merge(['class' => 'mt-0.5 h-4.5 w-4.5 cursor-pointer rounded-md border-slate-300 text-brand-500 shadow-sm transition-all focus:ring-brand-500/30 dark:border-white/20 dark:bg-navy-900/60']) }}>
    <span>
        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</span>
        @if($hint)<span class="block text-xs text-slate-400 dark:text-slate-500">{{ $hint }}</span>@endif
    </span>
</label>
