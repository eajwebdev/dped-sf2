<x-app-shell title="SF2 — Daily Attendance Report">
    <div class="mx-auto max-w-3xl rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-3">
            <h2 class="text-sm font-semibold">Generate School Form 2</h2>
            <p class="text-xs text-gray-400">Pick a class and month, then preview, print, or export.</p>
        </div>

        <form method="GET" action="" x-data="{ section: '' }"
              @submit.prevent="if(section){ window.location = '{{ url('reports/sf2') }}/' + section + '?year=' + document.getElementById('year').value + '&month=' + document.getElementById('month').value }">
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">
                <div class="sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Class</label>
                    <select id="section" x-model="section" required
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Select a class —</option>
                        @forelse ($sections as $s)
                            <option value="{{ $s->id }}">{{ $s->schoolYear->name }} · {{ $s->gradeLevel->name }} — {{ $s->name }}</option>
                        @empty
                            <option value="" disabled>No accessible classes</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Month</label>
                    <select id="month" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($m === $month)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                    <input id="year" type="number" value="{{ $year }}" min="2000" max="2100"
                           class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Open Report</button>
                </div>
            </div>
        </form>
    </div>
</x-app-shell>
