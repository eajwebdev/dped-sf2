<x-admin-layout title="Promotion">
    <x-slot name="breadcrumbs">Admin / Promotion</x-slot>

    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
        Promotion creates <b>new</b> enrollments in the target year — prior enrollments and attendance are never changed.
        Graduating-grade learners become <b>Graduated</b>. Create the target year's sections first, then map each class below.
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-400">Promote FROM</label>
            <select name="from_year_id" onchange="this.form.submit()" class="mt-1 rounded-lg border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm focus:border-brand-500 focus:ring-brand-500">
                @foreach ($years as $y)<option value="{{ $y->id }}" @selected($fromYear && $fromYear->id === $y->id)>{{ $y->name }}{{ $y->is_active ? ' (active)' : '' }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400">Promote TO</label>
            <select name="to_year_id" onchange="this.form.submit()" class="mt-1 rounded-lg border-gray-300 dark:border-white/15 dark:bg-navy-900 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">— Select target year —</option>
                @foreach ($years as $y)<option value="{{ $y->id }}" @selected($toYear && $toYear->id === $y->id)>{{ $y->name }}</option>@endforeach
            </select>
        </div>
    </form>

    @if (! $toYear)
        <x-card><p class="py-6 text-center text-gray-400">Select a target school year to map sections.</p></x-card>
    @else
        <form method="POST" action="{{ route('admin.promotion.promote') }}" onsubmit="return confirm('Run promotion from {{ $fromYear->name }} to {{ $toYear->name }}? This creates new enrollments.');">
            @csrf
            <input type="hidden" name="from_year_id" value="{{ $fromYear->id }}">
            <input type="hidden" name="to_year_id" value="{{ $toYear->id }}">

            <x-card title="Map each {{ $fromYear->name }} class to a {{ $toYear->name }} class">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                        <thead><tr class="text-left text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2">Source class</th><th class="px-3 py-2 text-center">Learners</th><th class="px-3 py-2">Next grade</th><th class="px-3 py-2">Target class ({{ $toYear->name }})</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse ($sourceSections as $sec)
                                @php $next = $sec->gradeLevel->nextGrade(); @endphp
                                <tr>
                                    <td class="px-3 py-2 font-medium">{{ $sec->gradeLevel->name }} — {{ $sec->name }}</td>
                                    <td class="px-3 py-2 text-center">{{ $sec->learners_count }}</td>
                                    <td class="px-3 py-2">
                                        @if ($sec->gradeLevel->is_graduating)
                                            <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[11px] text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">Graduating</span>
                                        @else
                                            {{ $next?->name ?? '—' }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($sec->gradeLevel->is_graduating)
                                            <span class="text-xs text-gray-400">Will be marked Graduated</span>
                                        @elseif (! $next)
                                            <span class="text-xs text-amber-500">No next grade defined</span>
                                        @else
                                            <select name="section_map[{{ $sec->id }}]" class="rounded-lg border-gray-300 dark:border-white/15 dark:bg-navy-900 text-xs py-1.5 focus:border-brand-500 focus:ring-brand-500">
                                                <option value="">— Skip —</option>
                                                @foreach (($targetSectionsByGrade[$next->id] ?? collect()) as $ts)
                                                    <option value="{{ $ts->id }}">{{ $ts->gradeLevel->name }} — {{ $ts->name }}</option>
                                                @endforeach
                                            </select>
                                            @if (($targetSectionsByGrade[$next->id] ?? collect())->isEmpty())
                                                <span class="ml-1 text-[11px] text-amber-500">No {{ $next->name }} sections in {{ $toYear->name }} yet</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-8 text-center text-gray-400">No sections in {{ $fromYear->name }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">Run Promotion</button>
                </div>
            </x-card>
        </form>
    @endif
</x-admin-layout>
