<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $q = trim((string) $request->get('q'));
        $results = ['students' => collect(), 'teachers' => collect(), 'sections' => collect(), 'subjects' => collect()];

        if (strlen($q) >= 2) {
            $like = "%{$q}%";

            $results['students'] = Student::query()
                ->where(fn ($w) => $w->where('lrn', 'like', $like)
                    ->orWhere('first_name', 'like', $like)->orWhere('last_name', 'like', $like)->orWhere('middle_name', 'like', $like))
                ->orderBy('last_name')->limit(15)->get();

            $results['teachers'] = Teacher::query()
                ->where(fn ($w) => $w->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like)->orWhere('employee_no', 'like', $like))
                ->orderBy('last_name')->limit(10)->get();

            $results['sections'] = Section::with(['gradeLevel', 'schoolYear'])
                ->where('name', 'like', $like)->orderBy('name')->limit(10)->get();

            $results['subjects'] = Subject::where('name', 'like', $like)->orWhere('code', 'like', $like)
                ->orderBy('name')->limit(10)->get();
        }

        return view('admin.search.index', compact('q', 'results'));
    }
}
