<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolYear;
use App\Services\SalesService;

class DashboardController extends Controller
{
    /** Owner console: sales figures plus each school and its active year. */
    public function __invoke(SalesService $sales)
    {
        return view('admin.dashboard', [
            'sales' => $sales->overview(),
            'schools' => School::withCount('users')->with('activeSchoolYear')->orderBy('name')->get(),
            'globalYear' => SchoolYear::current(),
        ]);
    }
}
