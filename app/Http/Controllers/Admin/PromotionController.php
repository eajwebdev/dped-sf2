<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $promotion) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $years = SchoolYear::orderByDesc('start_date')->get();
        $fromYear = SchoolYear::find($request->integer('from_year_id')) ?? SchoolYear::current() ?? $years->first();
        $toYear = SchoolYear::find($request->integer('to_year_id'))
            ?? $years->where('start_date', '>', $fromYear?->start_date)->sortBy('start_date')->first();

        $sourceSections = collect();
        if ($fromYear) {
            $sourceSections = Section::with(['gradeLevel'])
                ->where('school_year_id', $fromYear->id)
                ->withCount(['activeEnrollments as learners_count'])
                ->orderBy('grade_level_id')->orderBy('name')
                ->get();
        }

        // Candidate target sections grouped by grade level (for the next-grade dropdowns).
        $targetSectionsByGrade = collect();
        if ($toYear) {
            $targetSectionsByGrade = Section::with('gradeLevel')
                ->where('school_year_id', $toYear->id)
                ->orderBy('name')->get()
                ->groupBy('grade_level_id');
        }

        return view('admin.promotion.index', compact(
            'years', 'fromYear', 'toYear', 'sourceSections', 'targetSectionsByGrade'
        ));
    }

    public function promote(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'from_year_id' => ['required', 'exists:school_years,id'],
            'to_year_id' => ['required', 'different:from_year_id', 'exists:school_years,id'],
            'section_map' => ['array'],
            'section_map.*' => ['nullable', 'exists:sections,id'],
        ]);

        $from = SchoolYear::findOrFail($data['from_year_id']);
        $to = SchoolYear::findOrFail($data['to_year_id']);

        $result = $this->promotion->promote($from, $to, $data['section_map'] ?? [], $request->user());

        $msg = "Promotion complete: {$result['promoted']} promoted, {$result['graduated']} graduated"
            .", {$result['retained']} retained, {$result['skipped']} skipped.";

        return redirect()
            ->route('admin.promotion.index', ['from_year_id' => $from->id, 'to_year_id' => $to->id])
            ->with('success', $msg);
    }
}
