<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SectionRequest;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Teacher;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request)
    {
        $activeYear = SchoolYear::current();
        $yearId = $request->integer('school_year_id') ?: $activeYear?->id;

        $sections = Section::with(['gradeLevel', 'adviser', 'schoolYear'])
            ->withCount('enrollments')
            ->when($yearId, fn ($q) => $q->where('school_year_id', $yearId))
            ->orderBy('grade_level_id')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sections.index', [
            'sections' => $sections,
            'selectedYearId' => $yearId,
        ] + $this->formData());
    }

    public function create()
    {
        return $this->index(request())->with('openModal', 'create');
    }

    public function store(SectionRequest $request): RedirectResponse
    {
        $section = Section::create($request->validated());
        $this->audit->created($section, "Section {$section->name} created");

        return redirect()->route('admin.sections.index')->with('success', "Section {$section->name} created.");
    }

    public function edit(Section $section)
    {
        return $this->index(request())->with(['openModal' => 'edit', 'editModel' => $section]);
    }

    public function update(SectionRequest $request, Section $section): RedirectResponse
    {
        $original = $section->getOriginal();
        $section->update($request->validated());
        $this->audit->updated($section, $original);

        return redirect()->route('admin.sections.index')->with('success', "Section {$section->name} updated.");
    }

    public function destroy(Section $section): RedirectResponse
    {
        $this->authorize('delete', $section);
        $name = $section->name;
        $section->delete();
        $this->audit->deleted($section, "Section {$name} deleted");

        return redirect()->route('admin.sections.index')->with('success', "Section {$name} deleted.");
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'schoolYears' => SchoolYear::orderByDesc('start_date')->get(),
            'gradeLevels' => GradeLevel::orderBy('level_order')->get(),
            'teachers' => Teacher::where('is_active', true)->orderBy('last_name')->get(),
        ];
    }
}
