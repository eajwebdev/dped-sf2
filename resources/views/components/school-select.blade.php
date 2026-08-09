@props([
    'name' => 'school_id',
    'schools' => null,      // collection of School models for small local pickers
    'selected' => null,
    'selectedLabel' => null,
    'remoteUrl' => null,    // JSON endpoint for large remote lookups
    'minChars' => 2,
    'placeholder' => 'Search your school…',
    'required' => false,
    'dark' => false,        // dark-glass styling (register page); default is admin light/dark tokens
])

@php
    $schools = collect($schools ?? []);
    $selected = $selected ? (string) $selected : '';
    $options = $schools->map(fn ($s) => [
        'id' => $s->id,
        'label' => $s->name.($s->school_id ? ' (ID '.$s->school_id.')' : ''),
        'meta' => collect([$s->division, $s->region])->filter()->join(' • '),
    ])->values();

    if ($selected && $selectedLabel && ! $options->contains(fn ($option) => (string) $option['id'] === $selected)) {
        $options->push(['id' => $selected, 'label' => $selectedLabel, 'meta' => '']);
    }

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
        selectedId: @js($selected),
        options: @js($options),
        remoteUrl: @js($remoteUrl),
        minChars: @js((int) $minChars),
        loading: false,
        requestId: 0,
        get filtered() {
            if (this.remoteUrl) return this.options;

            const q = this.search.toLowerCase().trim();
            return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
        },
        get selectedLabel() {
            const hit = this.options.find(o => String(o.id) === String(this.selectedId));
            return hit ? hit.label : '';
        },
        get canSearch() {
            return this.search.trim().length >= this.minChars;
        },
        async searchRemote() {
            this.open = true;

            if (!this.remoteUrl) return;

            const q = this.search.trim();
            if (q.length < this.minChars) {
                this.options = this.selectedId
                    ? this.options.filter(o => String(o.id) === String(this.selectedId))
                    : [];
                return;
            }

            const currentRequest = ++this.requestId;
            this.loading = true;

            try {
                const response = await fetch(`${this.remoteUrl}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const payload = await response.json();

                if (currentRequest === this.requestId) {
                    this.options = payload.data || [];
                }
            } catch (error) {
                if (currentRequest === this.requestId) {
                    this.options = [];
                }
            } finally {
                if (currentRequest === this.requestId) {
                    this.loading = false;
                }
            }
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
        <input type="text" x-ref="search" x-model="search" @focus="open = true; searchRemote()" @input.debounce.250ms="searchRemote()"
               placeholder="{{ $placeholder }}" autocomplete="off"
               class="{{ $input }}">
    </div>

    <div x-show="open && !selectedId" x-cloak x-transition
         class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border shadow-lift {{ $panel }}">
        <template x-for="o in filtered" :key="o.id">
            <button type="button" @click="choose(o)"
                    class="block w-full px-4 py-2.5 text-left text-sm transition-colors {{ $row }}">
                <span class="block truncate font-medium" x-text="o.label"></span>
                <span x-show="o.meta" class="mt-0.5 block truncate text-[11px] opacity-70" x-text="o.meta"></span>
            </button>
        </template>
        <p x-show="remoteUrl && !canSearch && !loading" class="px-4 py-3 text-xs {{ $dark ? 'text-slate-500' : 'text-slate-400' }}">
            Type at least {{ (int) $minChars }} characters to search.
        </p>
        <p x-show="loading" class="px-4 py-3 text-xs {{ $dark ? 'text-slate-500' : 'text-slate-400' }}">Searching schools…</p>
        <p x-show="!loading && filtered.length === 0 && (!remoteUrl || canSearch)" class="px-4 py-3 text-xs {{ $dark ? 'text-slate-500' : 'text-slate-400' }}">No school matches — try fewer words or the School ID.</p>
    </div>
</div>
