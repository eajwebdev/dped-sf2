<?php

use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BillingController as AdminBillingController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SchoolYearController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentIoController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassScanController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCardController;
use App\Http\Controllers\QrCheckinController;
use App\Http\Controllers\Sf2Controller;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Teacher\CuttingClassController as TeacherCuttingClassController;
use App\Http\Controllers\Teacher\SectionController as TeacherSectionController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\SubjectController as TeacherSubjectController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherScheduleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Signed-in users go to their dashboard; guests see the public landing page.
    return Auth::check() ? redirect()->route('dashboard') : view('public.landing');
})->name('landing');

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

    // Account status page for pending / rejected registrations (no subscription gate).
    Route::get('/account/pending', [SubscriptionController::class, 'pending'])->name('account.pending');
});

/*
|--------------------------------------------------------------------------
| Subscription (approved teachers, gate-free so lapsed users can pay)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/subscribe', [SubscriptionController::class, 'show'])->name('subscribe.show');
    Route::post('/subscribe/checkout', [SubscriptionController::class, 'checkout'])->name('subscribe.checkout');
    Route::get('/subscribe/success', [SubscriptionController::class, 'success'])->name('subscribe.success');
    Route::get('/subscribe/cancel', [SubscriptionController::class, 'cancel'])->name('subscribe.cancel');
});

// PayMongo server-to-server webhook (no auth, CSRF-exempt).
Route::post('/subscription/webhook', [SubscriptionController::class, 'webhook'])->name('subscription.webhook');

/*
|--------------------------------------------------------------------------
| Public key-gated class scanner (the QR key is the credential — no login)
|--------------------------------------------------------------------------
*/
// The scan portal is key-gated now: /portal lands on class-key entry.
Route::redirect('/portal', '/class-scan')->name('portal');

Route::get('/class-scan', [ClassScanController::class, 'enter'])->name('class-scan.enter');
Route::post('/class-scan', [ClassScanController::class, 'unlock'])
    ->middleware('throttle:20,1')->name('class-scan.unlock');
Route::get('/class-scan/session', [ClassScanController::class, 'show'])->name('class-scan.show');
Route::post('/class-scan/checkin', [ClassScanController::class, 'checkIn'])
    ->middleware('throttle:240,1')->name('class-scan.checkin');
Route::post('/class-scan/exit', [ClassScanController::class, 'exit'])->name('class-scan.exit');

/*
|--------------------------------------------------------------------------
| Attendance (teachers + admins) & Teacher dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'subscription'])->group(function () {
    Route::get('/teacher', TeacherDashboardController::class)->name('teacher.dashboard');

    // Teacher-owned, school-scoped roster & subject management.
    // Teachers can open their own advisory class for the active school year.
    Route::post('/sections', [TeacherSectionController::class, 'store'])->name('teacher.sections.store');

    // Advisory learners who skipped a period today.
    Route::get('/cutting', [TeacherCuttingClassController::class, 'index'])->name('teacher.cutting.index');

    Route::resource('students', TeacherStudentController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('teacher.students');
    Route::resource('subjects', TeacherSubjectController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names('teacher.subjects');

    // Start Class → live QR-attendance session.
    Route::post('/class-sessions/start', [ClassSessionController::class, 'start'])->name('class-sessions.start');
    Route::get('/class-sessions/{session}', [ClassSessionController::class, 'show'])->name('class-sessions.show');
    Route::post('/class-sessions/{session}/end', [ClassSessionController::class, 'end'])->name('class-sessions.end');

    // Weekly teaching schedule (calendar).
    Route::get('/schedule', [TeacherScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule', [TeacherScheduleController::class, 'store'])->name('schedule.store');
    Route::patch('/schedule/{schedule}', [TeacherScheduleController::class, 'update'])->name('schedule.update');
    Route::delete('/schedule/{schedule}', [TeacherScheduleController::class, 'destroy'])->name('schedule.destroy');

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
    // Renders the SF2 as an inline PDF (DomPDF) — no HTML view, no Excel.
    Route::get('/reports/sf2/{section}', [Sf2Controller::class, 'show'])->name('reports.sf2.show');
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

        // Schools (SaaS tenants) that teachers register into.
        Route::resource('schools', SchoolController::class)->except('show');

        // Teacher self-registration approvals.
        Route::get('registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::post('registrations/{user}/approve', [RegistrationController::class, 'approve'])->name('registrations.approve');
        Route::post('registrations/{user}/reject', [RegistrationController::class, 'reject'])->name('registrations.reject');

        // School years + lifecycle actions.
        Route::post('school-years/{school_year}/activate', [SchoolYearController::class, 'activate'])->name('school-years.activate');
        Route::post('school-years/{school_year}/close', [SchoolYearController::class, 'close'])->name('school-years.close');
        Route::post('school-years/{school_year}/archive', [SchoolYearController::class, 'archive'])->name('school-years.archive');
        Route::resource('school-years', SchoolYearController::class)->except('show');

        Route::resource('grade-levels', GradeLevelController::class)->except('show');
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::resource('teachers', TeacherController::class)->except('show');
        Route::post('teachers/{teacher}/free-access', [TeacherController::class, 'toggleFreeAccess'])->name('teachers.free-access');
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

        // Subscription pricing / discount.
        Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');

        // Payment history.
        Route::get('billing', [AdminBillingController::class, 'index'])->name('billing.index');

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
