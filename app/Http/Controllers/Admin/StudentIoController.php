<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

class StudentIoController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function export(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $this->audit->log('export', null, 'Students exported to Excel');

        return Excel::download(
            new StudentsExport($request->get('q'), $request->get('status')),
            'students-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $import = new StudentsImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (ExcelValidationException $e) {
            $count = count($e->failures());

            return back()->with('error', "Import stopped: {$count} row(s) failed validation (check LRN, first & last name).");
        }

        $this->audit->log('import', null, "Imported {$import->imported} students from Excel");

        return back()->with('success', "Imported {$import->imported} new student(s).");
    }

    /** Downloadable blank template with the expected headings. */
    public function template()
    {
        $headings = ['lrn', 'first_name', 'middle_name', 'last_name', 'suffix', 'gender', 'birthdate', 'address', 'guardian_name', 'guardian_contact'];

        return Excel::download(new class($headings) implements FromArray, WithHeadings
        {
            public function __construct(private array $headings) {}

            public function array(): array
            {
                return [['123456789012', 'Juan', 'Reyes', 'Dela Cruz', '', 'Male', '2013-05-01', 'Sample Address', 'Maria Dela Cruz', '09171234567']];
            }

            public function headings(): array
            {
                return $this->headings;
            }
        }, 'students-import-template.xlsx');
    }
}
