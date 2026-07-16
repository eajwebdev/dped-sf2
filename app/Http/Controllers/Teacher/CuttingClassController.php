<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\CuttingClassService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CuttingClassController extends Controller
{
    public function __construct(private readonly CuttingClassService $cutting) {}

    /** Who among my advisory learners skipped a period on the given day. */
    public function index(Request $request)
    {
        $request->validate(['date' => ['nullable', 'date']]);

        $date = $request->filled('date')
            ? Carbon::parse($request->get('date'))->startOfDay()
            : Carbon::today();

        return view('teacher.cutting', [
            'rows' => $this->cutting->forAdviser($request->user(), $date),
            'date' => $date,
        ]);
    }
}
