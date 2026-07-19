<x-app-shell title="SF3 — Books Issued and Returned">
    <div class="mx-auto max-w-2xl">
        <div class="rounded-card border border-slate-200/80 bg-white shadow-soft dark:border-white/10 dark:bg-navy-800">
            <div class="border-b border-slate-200/80 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">SF3 — Books Issued and Returned</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Record the textbooks you hand out on the issuance screen; the SF3 prints from those records.
                </p>
            </div>

            <div class="p-6">
                @forelse ($sections as $s)
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 py-3 last:border-0 dark:border-white/5">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $s->gradeLevel->name }} — {{ $s->name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">SY {{ $s->schoolYear->name }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('books.index', $s) }}"
                               class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:text-slate-200 dark:hover:bg-white/5">
                                Manage books
                            </a>
                            <a href="{{ route('reports.sf3.show', $s) }}" target="_blank"
                               class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                                Generate SF3
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        No advisory classes — SF3 is recorded by the class adviser only.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-shell>
