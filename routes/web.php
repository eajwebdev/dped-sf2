<?php

use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SchoolYearController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentIoController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCardController;
use App\Http\Controllers\QrCheckinController;
use App\Http\Controllers\ScanPortalController;
use App\Http\Controllers\Sf2Controller;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherScheduleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Guests go straight to the login screen; signed-in users to their dashboard.
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Role-aware landing: send admins to the admin dashboard, teachers to theirs.
Route::get('/dashboard', function () {
    return Auth::user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('teacher.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Attendance (teachers + admins) & Teacher dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/teacher', TeacherDashboardController::class)->name('teacher.dashboard');

    // Weekly teaching schedule (calendar).
    Route::get('/schedule', [TeacherScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule', [TeacherScheduleController::class, 'store'])->name('schedule.store');
    Route::patch('/schedule/{schedule}', [TeacherScheduleController::class, 'update'])->name('schedule.update');
    Route::delete('/schedule/{schedule}', [TeacherScheduleController::class, 'destroy'])->name('schedule.destroy');

    // Full-screen QR scan portal (students scan themselves present).
    Route::get('/portal', ScanPortalController::class)->name('portal');

    // Downloadable QR ID cards (name + school year + section).
    Route::get('/qr-cards/section/{section}', [QrCardController::class, 'section'])->name('qr-cards.section');
    Route::get('/qr-cards/student/{student}', [QrCardController::class, 'student'])->name('qr-cards.student');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{section}', [AttendanceController::class, 'sheet'])->name('attendance.sheet');
    Route::post('/attendance/{section}/save', [AttendanceController::class, 'save'])
        ->middleware('throttle:120,1')->name('attendance.save');
    Route::post('/attendance/{section}/unlock', [AttendanceController::class, 'unlock'])->name('attendance.unlock');

    // QR check-in.
    Route::get('/attendance/{section}/scan', [QrCheckinController::class, 'scan'])->name('attendance.scan');
    Route::post('/attendance/{section}/checkin', [QrCheckinController::class, 'checkIn'])
        ->middleware('throttle:240,1')->name('attendance.checkin');

    // SF2 Daily Attendance Report of Learners.
    Route::get('/reports/sf2', [Sf2Controller::class, 'index'])->name('reports.sf2.index');
    Route::get('/reports/sf2/{section}', [Sf2Controller::class, 'show'])->name('reports.sf2.show');
    Route::get('/reports/sf2/{section}/pdf', [Sf2Controller::class, 'pdf'])->name('reports.sf2.pdf');
    Route::get('/reports/sf2/{section}/excel', [Sf2Controller::class, 'excel'])->name('reports.sf2.excel');
});

/*
|--------------------------------------------------------------------------
| Administrator
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        // School years + lifecycle actions.
        Route::post('school-years/{school_year}/activate', [SchoolYearController::class, 'activate'])->name('school-years.activate');
        Route::post('school-years/{school_year}/close', [SchoolYearController::class, 'close'])->name('school-years.close');
        Route::post('school-years/{school_year}/archive', [SchoolYearController::class, 'archive'])->name('school-years.archive');
        Route::resource('school-years', SchoolYearController::class)->except('show');

        Route::resource('grade-levels', GradeLevelController::class)->except('show');
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::resource('teachers', TeacherController::class)->except('show');
        Route::resource('sections', SectionController::class)->except('show');

        // Students (+ import/export).
        Route::get('students/export', [StudentIoController::class, 'export'])->name('students.export');
        Route::get('students/import/template', [StudentIoController::class, 'template'])->name('students.import.template');
        Route::post('students/import', [StudentIoController::class, 'import'])->name('students.import');
        Route::resource('students', StudentController::class);

        // Global search.
        Route::get('search', [SearchController::class, 'index'])->name('search.index');

        // Audit logs.
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // End-of-year promotion.
        Route::get('promotion', [PromotionController::class, 'index'])->name('promotion.index');
        Route::post('promotion', [PromotionController::class, 'promote'])->name('promotion.promote');

        // Enrollment (roster-driven, per school year).
        Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::post('enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
        Route::patch('enrollments/{enrollment}/transfer', [EnrollmentController::class, 'transfer'])->name('enrollments.transfer');
        Route::patch('enrollments/{enrollment}/status', [EnrollmentController::class, 'changeStatus'])->name('enrollments.status');
        Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

        // Subject + teacher assignments (scoped to a section).
        Route::get('sections/{section}/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('sections/{section}/assignments', [AssignmentController::class, 'storeSubject'])->name('assignments.subjects.store');
        Route::delete('subject-assignments/{subjectAssignment}', [AssignmentController::class, 'destroySubject'])->name('assignments.subjects.destroy');
        Route::post('subject-assignments/{subjectAssignment}/teachers', [AssignmentController::class, 'assignTeacher'])->name('assignments.teachers.store');
        Route::delete('teacher-assignments/{teacherSubjectAssignment}', [AssignmentController::class, 'unassignTeacher'])->name('assignments.teachers.destroy');
    });

require __DIR__.'/auth.php';
