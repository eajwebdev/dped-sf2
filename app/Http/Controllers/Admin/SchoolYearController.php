<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolYearRequest;
use App\Models\School;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use App\Services\AuditLogger;
use App\Services\SchoolCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SchoolYearController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly SchoolCalendarService $calendar,
    ) {}

    public function index()
    {
        $schoolYears = SchoolYear::withCount(['sections', 'enrollments'])
            ->orderByDesc('start_date')
            ->paginate(15);

        $schools = School::active()->with('activeSchoolYear')->orderBy('name')->get();

        return view('admin.school-years.index', compact('schoolYears', 'schools'));
    }

    public function create()
    {
        return $this->index()->with('openModal', 'create');
    }

    public function store(SchoolYearRequest $request): RedirectResponse
    {
        $schoolYear = SchoolYear::create($request->validated() + ['status' => SchoolYear::STATUS_OPEN]);
        $this->calendar->generate($schoolYear);
        $this->audit->created($schoolYear, "School year {$schoolYear->name} created");

        return redirect()->route('admin.school-years.index')
            ->with('success', "School year {$schoolYear->name} created.");
    }

    public function edit(SchoolYear $schoolYear)
    {
        return $this->index()->with(['openModal' => 'edit', 'editModel' => $schoolYear]);
    }

    public function update(SchoolYearRequest $request, SchoolYear $schoolYear): RedirectResponse
    {
        $this->authorize('update', $schoolYear);
        $original = $schoolYear->getOriginal();

        // SchoolYear model regenerates the calendar itself when the dates change.
        $schoolYear->update($request->validated());

        $this->audit->updated($schoolYear, $original);

        return redirect()->route('admin.school-years.index')
            ->with('success', "School year {$schoolYear->name} updated.");
    }

    public function destroy(SchoolYear $schoolYear): RedirectResponse
    {
        $this->authorize('delete', $schoolYear);
        $name = $schoolYear->name;
        $schoolYear->delete();
        $this->audit->deleted($schoolYear, "School year {$name} deleted");

        return redirect()->route('admin.school-years.index')
            ->with('success', "School year {$name} deleted.");
    }

    /** Make this the single active school year. */
    /**
     * Activate a year globally (all schools) or for one school only.
     * "All" also resets per-school overrides so every school follows it.
     */
    public function activate(Request $request, SchoolYear $schoolYear): RedirectResponse
    {
        $this->authorize('update', $schoolYear);

        $data = $request->validate([
            'scope' => ['nullable', 'in:all,school'],
            'school_id' => ['required_if:scope,school', 'nullable', 'exists:schools,id'],
        ]);

        if (($data['scope'] ?? 'all') === 'school') {
            $school = School::findOrFail($data['school_id']);
            $school->update(['active_school_year_id' => $schoolYear->id]);
            $schoolYear->update(['status' => SchoolYear::STATUS_OPEN]);
            $this->audit->log('activated', $schoolYear, "SY {$schoolYear->name} set active for {$school->name}");

            return back()->with('success', "SY {$schoolYear->name} is now active for {$school->name} only.");
        }

        DB::transaction(function () use ($schoolYear) {
            SchoolYear::where('is_active', true)->update(['is_active' => false]);
            $schoolYear->update(['is_active' => true, 'status' => SchoolYear::STATUS_OPEN]);
            School::query()->update(['active_school_year_id' => $schoolYear->id]);
        });
        SchoolYear::forgetCurrent();
        $this->audit->log('activated', $schoolYear, "School year {$schoolYear->name} set active for all schools");

        return back()->with('success', "SY {$schoolYear->name} is now active for all schools.");
    }

    public function close(SchoolYear $schoolYear): RedirectResponse
    {
        $this->authorize('update', $schoolYear);
        $schoolYear->update(['status' => SchoolYear::STATUS_CLOSED, 'is_active' => false]);
        SchoolYear::forgetCurrent();
        $this->audit->log('closed', $schoolYear, "School year {$schoolYear->name} closed");

        return back()->with('success', "SY {$schoolYear->name} closed.");
    }

    public function archive(SchoolYear $schoolYear): RedirectResponse
    {
        $this->authorize('update', $schoolYear);
        $schoolYear->update(['status' => SchoolYear::STATUS_ARCHIVED, 'is_active' => false]);
        SchoolYear::forgetCurrent();
        $this->audit->log('archived', $schoolYear, "School year {$schoolYear->name} archived");

        return back()->with('success', "SY {$schoolYear->name} archived.");
    }
}
