<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Models\School;
use App\Services\AuditLogger;
use App\Services\SchoolMasterlistImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $schools = School::withCount('users')->orderBy('name')->paginate(20);

        return view('admin.schools.index', compact('schools'));
    }

    public function create()
    {
        return $this->index()->with('openModal', 'create');
    }

    public function store(SchoolRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $this->storeLogo($request);
        }

        $school = School::create($data);
        $this->audit->created($school);

        return redirect()->route('admin.schools.index')->with('success', "{$school->name} added.");
    }

    public function edit(School $school)
    {
        return $this->index()->with(['openModal' => 'edit', 'editModel' => $school]);
    }

    public function update(SchoolRequest $request, School $school): RedirectResponse
    {
        $original = $school->getOriginal();
        $data = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            // Logos live directly in public/ (no storage:link needed) so the
            // PDF renderer and web both read the same real file.
            if ($school->logo_path && file_exists(public_path($school->logo_path))) {
                @unlink(public_path($school->logo_path));
            }
            $data['logo_path'] = $this->storeLogo($request);
        }

        $school->update($data);
        $this->audit->updated($school, $original);

        return redirect()->route('admin.schools.index')->with('success', "{$school->name} updated.");
    }

    /** Save the uploaded logo under public/school-logos, returning its public-relative path. */
    private function storeLogo(Request $request): string
    {
        $file = $request->file('logo');
        $name = uniqid('school-', true).'.'.$file->getClientOriginalExtension();
        $file->move(public_path('school-logos'), $name);

        return 'school-logos/'.$name;
    }

    public function destroy(School $school): RedirectResponse
    {
        $name = $school->name;

        // Soft delete: keep the logo file so a restored school keeps its branding.
        $school->delete();
        $this->audit->deleted($school);

        return redirect()->route('admin.schools.index')->with('success', "{$name} deleted.");
    }

    public function importMasterlist(Request $request, SchoolMasterlistImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'masterlist' => ['nullable', 'file', 'mimes:xls,xlsx', 'max:10240'],
            'sheet' => ['nullable', 'string', 'max:100'],
        ]);

        $path = $request->hasFile('masterlist')
            ? $request->file('masterlist')->getRealPath()
            : public_path('masterlist_of_schools_based_on_school_year_-_original.xls');

        $sheet = $validated['sheet'] ?? 'FOR IMPORT';

        if ($sheet === 'FOR IMPORT') {
            $importer->populateImportSheet($path);
        }

        $result = $importer->import($path, $sheet);

        $this->audit->log(
            'school_masterlist_imported',
            null,
            "Imported {$result['total']} schools from {$result['sheet']}",
            null,
            $result,
        );

        return redirect()
            ->route('admin.schools.index')
            ->with('success', "Imported {$result['total']} schools: {$result['created']} created, {$result['updated']} updated, {$result['skipped']} skipped.");
    }
}
