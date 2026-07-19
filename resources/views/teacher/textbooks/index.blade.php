<x-app-shell :title="null" wide>
    <div x-data="{
            cell: null,
            openCell(data) { this.cell = data; $nextTick(() => $refs.issued?.focus()); },
        }">

        {{-- Header --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('reports.sf3.index') }}" class="text-xs text-gray-400 hover:text-gray-600">&larr; SF3 classes</a>
                <h1 class="text-lg font-semibold">Books — {{ $section->gradeLevel->name }} {{ $section->name }}</h1>
                <p class="text-xs text-gray-400">SY {{ $section->schoolYear->name }} · {{ $roster->count() }} learners · Records feed straight into the SF3.</p>
            </div>
            <a href="{{ route('reports.sf3.show', $section) }}" target="_blank"
               class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Generate SF3</a>
        </div>

        @if (session('success'))
            <div class="mb-3 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-3 rounded-lg border border-red-300 bg-red-50 px-4 py-2.5 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">{{ session('error') }}</div>
        @endif

        {{-- Book manager --}}
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-navy-800">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 flex-1 space-y-1.5">
                    @forelse ($books as $book)
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $book->subject_area }}</span>
                            <span class="min-w-0 flex-shrink truncate text-gray-600 dark:text-gray-300">{{ $book->title }}</span>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500 dark:bg-white/10 dark:text-gray-400">{{ $book->issued_count }}/{{ $roster->count() }} issued</span>

                            <form method="POST" action="{{ route('books.issue-all', [$section, $book]) }}" class="flex items-center gap-1.5">
                                @csrf
                                <input type="date" name="issued_at" value="{{ now()->toDateString() }}" required
                                       class="rounded-md border-gray-300 py-0.5 text-xs dark:border-white/15 dark:bg-navy-900">
                                <button class="rounded-md border border-brand-300 px-2 py-1 text-[11px] font-semibold text-brand-600 hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10">
                                    Issue to all
                                </button>
                            </form>

                            <form method="POST" action="{{ route('books.destroy', [$section, $book]) }}"
                                  onsubmit="return confirm('Remove &quot;{{ $book->title }}&quot;?');">
                                @csrf @method('DELETE')
                                <button class="rounded-md px-1.5 py-1 text-[11px] font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">Remove</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No textbooks yet — add the titles you hand out below.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('books.store', $section) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-medium text-gray-400">Subject area</label>
                        <input name="subject_area" required maxlength="80" placeholder="e.g. Mathematics 8"
                               class="mt-0.5 w-36 rounded-lg border-gray-300 text-sm dark:border-white/15 dark:bg-navy-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-400">Book title</label>
                        <input name="title" required maxlength="255" placeholder="e.g. Math Learner's Module 8"
                               class="mt-0.5 w-52 rounded-lg border-gray-300 text-sm dark:border-white/15 dark:bg-navy-900">
                    </div>
                    <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700 dark:bg-white/10 dark:hover:bg-white/20">Add book</button>
                </form>
            </div>
        </div>

        {{-- Issuance grid --}}
        @if ($books->isNotEmpty() && $roster->isNotEmpty())
            <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-white/10 dark:bg-navy-800">
                <table class="w-full text-xs">
                    <thead class="border-b border-gray-200 bg-gray-50 text-left dark:border-white/10 dark:bg-navy-800/60">
                        <tr>
                            <th class="sticky left-0 bg-gray-50 px-3 py-2.5 font-bold dark:bg-navy-800">Learner</th>
                            @foreach ($books as $book)
                                <th class="px-2 py-2.5 text-center font-bold" title="{{ $book->title }}">
                                    {{ Str::limit($book->subject_area, 14) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($roster as $enrollment)
                            @php $byBook = $enrollment->textbookIssuances->keyBy('textbook_id'); @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-navy-700/30">
                                <td class="sticky left-0 whitespace-nowrap bg-white px-3 py-1.5 font-medium dark:bg-navy-800">
                                    {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                                    <span class="ml-1 text-[10px] text-gray-400">{{ $enrollment->student->gender === 'Male' ? 'M' : 'F' }}</span>
                                </td>
                                @foreach ($books as $book)
                                    @php $issue = $byBook->get($book->id); @endphp
                                    <td class="px-1 py-1 text-center">
                                        <button type="button"
                                                @click="openCell(@js([
                                                    'textbook_id' => $book->id,
                                                    'student_enrollment_id' => $enrollment->id,
                                                    'learner' => $enrollment->student->last_name.', '.$enrollment->student->first_name,
                                                    'book' => $book->title,
                                                    'issued_at' => $issue?->issued_at?->toDateString(),
                                                    'returned_at' => $issue?->returned_at?->toDateString(),
                                                    'return_code' => $issue?->return_code,
                                                    'action_code' => $issue?->action_code,
                                                    'remarks' => $issue?->remarks,
                                                ]))"
                                                class="w-full cursor-pointer rounded-md px-1.5 py-1 text-[11px] font-semibold transition-colors
                                                    {{ $issue?->returned_at ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300'
                                                        : ($issue?->return_code ? 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300'
                                                        : ($issue?->issued_at ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-300'
                                                        : 'text-gray-300 hover:bg-gray-100 dark:text-gray-600 dark:hover:bg-white/5')) }}">
                                            @if ($issue?->returned_at) ✓ returned
                                            @elseif ($issue?->return_code) {{ $issue->return_code }}
                                            @elseif ($issue?->issued_at) out {{ $issue->issued_at->format('m/d') }}
                                            @else —
                                            @endif
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-[11px] text-gray-400">
                Click any cell to record an issue date, a return, or a lost-book code.
                <span class="ml-2">— not issued</span>
                <span class="ml-2 text-amber-600">out = issued, not yet returned</span>
                <span class="ml-2 text-emerald-600">✓ = returned</span>
                <span class="ml-2 text-red-500">FM/TDO/NEG = lost</span>
            </p>
        @endif

        {{-- Cell editor --}}
        <div x-show="cell" x-cloak @keydown.escape.window="cell = null"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" x-transition.opacity>
            <div @click.outside="cell = null" class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl dark:bg-navy-800">
                <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="cell?.learner"></p>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400" x-text="cell?.book"></p>

                <form method="POST" action="{{ route('books.cell', $section) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="textbook_id" :value="cell?.textbook_id">
                    <input type="hidden" name="student_enrollment_id" :value="cell?.student_enrollment_id">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-gray-400">Date issued</label>
                            <input x-ref="issued" type="date" name="issued_at" :value="cell?.issued_at"
                                   class="mt-0.5 w-full rounded-lg border-gray-300 text-sm dark:border-white/15 dark:bg-navy-900">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-400">Date returned</label>
                            <input type="date" name="returned_at" :value="cell?.returned_at"
                                   class="mt-0.5 w-full rounded-lg border-gray-300 text-sm dark:border-white/15 dark:bg-navy-900">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-gray-400">Lost-book code <span class="font-normal">(instead of a return date)</span></label>
                            <select name="return_code" class="mt-0.5 w-full rounded-lg border-gray-300 text-sm dark:border-white/15 dark:bg-navy-900">
                                <option value="">—</option>
                                @foreach ($returnCodes as $code => $label)
                                    <option value="{{ $code }}" :selected="cell?.return_code === '{{ $code }}'">{{ $code }} — {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-400">Action taken</label>
                            <select name="action_code" class="mt-0.5 w-full rounded-lg border-gray-300 text-sm dark:border-white/15 dark:bg-navy-900">
                                <option value="">—</option>
                                @foreach ($actionCodes as $code => $label)
                                    <option value="{{ $code }}" :selected="cell?.action_code === '{{ $code }}'">{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-gray-400">Remarks</label>
                        <input name="remarks" maxlength="255" :value="cell?.remarks"
                               class="mt-0.5 w-full rounded-lg border-gray-300 text-sm dark:border-white/15 dark:bg-navy-900">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" @click="cell = null"
                                class="rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">Cancel</button>
                        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-shell>
