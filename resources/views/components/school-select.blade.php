@props([
    'name' => 'school_id',
    'schools',              // collection of School models
    'selected' => null,
    'placeholder' => 'Search your school…',
    'required' => false,
    'dark' => false,        // dark-glass styling (register page); default is admin light/dark tokens
])

@php
    $options = $schools->map(fn ($s) => [
        'id' => $s->id,
        'label' => $s->name.($s->school_id ? ' (ID '.$s->school_id.')' : ''),
    ])->values();
    $input = $dark
        ? 'block w-full rounded-xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 transition focus:border-brand-400 focus:bg-white/10 focus:outline-none focus:ring-4 focus:ring-brand-500/20'
        : 'input';
    $panel = $dark
        ? 'border-white/15 bg-navy-800'
        : 'border-slate-200 bg-white dark:border-white/10 dark:bg-navy-800';
    $row = $dark
        ? 'text-slate-200 hover:bg-white/10'
        : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5';
@endphp

{{-- Searchable school picker: type to filter, click to choose; the hidden input carries the id. --}}
<div x-data="{
        open: false,
        search: '',
        selectedId: @js($selected ? (string) $selected : ''),
        options: @js($options),
        get filtered() {
            const q = this.search.toLowerCase().trim();
            return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
        },
        get selectedLabel() {
            const hit = this.options.find(o => String(o.id) === String(this.selectedId));
            return hit ? hit.label : '';
        },
        choose(o) { this.selectedId = String(o.id); this.search = ''; this.open = false; },
        clear() { this.selectedId = ''; this.search = ''; this.$nextTick(() => this.$refs.search.focus()); },
     }"
     @click.outside="open = false" @keydown.escape="open = false"
     class="relative">

    <input type="hidden" name="{{ $name }}" :value="selectedId" @if($required) x-bind:required="!selectedId" @endif>

    {{-- Closed state: shows the chosen school --}}
    <button type="button" x-show="selectedId" x-cloak @click="clear()"
            class="{{ $input }} flex items-center justify-between gap-2 text-left">
        <span x-text="selectedLabel" class="truncate"></span>
        <svg class="h-4 w-4 shrink-0 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    {{-- Open/search state --}}
    <div x-show="!selectedId">
        <input type="text" x-ref="search" x-model="search" @focus="open = true" @input="open = true"
               placeholder="{{ $placeholder }}" autocomplete="off"
               class="{{ $input }}">
    </div>

    <div x-show="open && !selectedId" x-cloak x-transition
         class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border shadow-lift {{ $panel }}">
        <template x-for="o in filtered" :key="o.id">
            <button type="button" @click="choose(o)"
                    class="block w-full px-4 py-2.5 text-left text-sm transition-colors {{ $row }}"
                    x-text="o.label"></button>
        </template>
        <p x-show="filtered.length === 0" class="px-4 py-3 text-xs {{ $dark ? 'text-slate-500' : 'text-slate-400' }}">No school matches — try fewer words.</p>
    </div>
</div>
