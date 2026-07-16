# Graph Report - .  (2026-07-17)

## Corpus Check
- 293 files · ~223,598 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1287 nodes · 2888 edges · 197 communities (160 shown, 37 thin omitted)
- Extraction: 92% EXTRACTED · 8% INFERRED · 0% AMBIGUOUS · INFERRED: 222 edges (avg confidence: 0.8)
- Token cost: 136,057 input · 0 output

## Community Hubs (Navigation)
- Admin Console Controllers
- Enrollment Management
- School Year Management
- QR ID Cards
- Grade Levels Admin
- Auth & Sessions
- Audit Logging
- Dashboard & SF2 Reporting
- Frontend NPM Toolchain
- Schools Admin
- Database Factories
- Class Sessions (Start Class)
- Subjects Admin
- UI Modernization Docs
- Teacher Subject Assignment
- Scan Portal (Legacy)
- User Model & Roles
- Teacher Search & Model
- App Models Setting
- App Http Controllers
- Composer Autoload Dev
- Ref Php Artisan
- App Http Requests
- Tests Feature Attendancetest
- App Http Controllers
- App Services Auditlogger
- App Http Middleware
- App Exports Sf2Export
- App Exports Studentsexport
- Tests Feature Adminaccesstest
- App Imports Studentsimport
- Composer Require Dev
- Tests Feature Sf2Reporttest
- App Http Controllers
- App Services Paymongoservice
- App Models User
- Ref Php Artisan
- Resources Js App
- Tests Feature Subscriptiontest
- App Http Controllers
- App Http Controllers
- App Models Auditlog
- App Policies Studentpolicy
- App Policies Teacherpolicy
- Composer Allow Plugins
- Composer Require Laravel
- App Http Requests
- App Providers Appserviceprovider
- Public Logo Blue
- App Http Controllers
- Composer Psr 4
- Public Eaj Primary
- Tests Feature Auth
- Tests Feature Auth
- Ref Php Artisan
- Tests Unit Exampletest
- Profile Partials Update
- Public Eaj Appicon
- Tests Feature Auth
- Tests Feature Auth
- Composer Extra
- Resources Views Layouts
- Resources Views Components
- Resources Views Components
- Admin Items Form
- Admin Students Form
- Readme Laravel Framework
- Resources Views Admin
- Resources Views Admin
- Resources Views Auth
- Resources Views Layouts
- Resources Views Reports
- Resources Views Reports
- Public Robots Allow

## God Nodes (most connected - your core abstractions)
1. `User` - 142 edges
2. `Section` - 102 edges
3. `SchoolYear` - 91 edges
4. `Controller` - 74 edges
5. `Student` - 62 edges
6. `AuditLogger` - 58 edges
7. `StudentEnrollment` - 51 edges
8. `GradeLevel` - 43 edges
9. `TestCase` - 40 edges
10. `Attendance` - 37 edges

## Surprising Connections (you probably didn't know these)
- `Modal Conversion Pattern (inline CRUD modals)` --semantically_similar_to--> `Modal-Based Forms`  [INFERRED] [semantically similar]
  MODAL_CONVERSION_GUIDE.md → UI_MODERNIZATION_GUIDE.md
- `x-icon-btn Component` --semantically_similar_to--> `Icon-Based Actions (view/edit/delete)`  [INFERRED] [semantically similar]
  MODAL_CONVERSION_GUIDE.md → UI_MODERNIZATION_GUIDE.md
- `Modal Markup Pattern (fade + scale, backdrop, ESC close)` --semantically_similar_to--> `Modal-Based Forms`  [INFERRED] [semantically similar]
  STYLES_REFERENCE.md → UI_MODERNIZATION_GUIDE.md
- `Modal + Icon Conversion Guide` --conceptually_related_to--> `UI Modernization Checklist`  [INFERRED]
  MODAL_CONVERSION_GUIDE.md → MODERNIZATION_CHECKLIST.md
- `Styles & Components Reference` --shares_data_with--> `Indigo/Emerald/Red Color Scheme`  [INFERRED]
  STYLES_REFERENCE.md → MODERNIZATION_SUMMARY.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Reusable Blade Component Library** — modernization_checklist_button_component, modernization_checklist_badge_component, modernization_checklist_delete_confirm_btn, modernization_checklist_card_component, modernization_checklist_admin_layout_component [EXTRACTED 1.00]
- **Modern CRUD Modal UX Flow** — ui_modernization_guide_modal_forms, ui_modernization_guide_alpine_js, ui_modernization_guide_icon_based_actions, modernization_summary_sweetalert2 [INFERRED 0.75]
- **UI Modernization Documentation Suite** — modal_conversion_guide_modal_icon_conversion_guide, modernization_checklist_ui_modernization_checklist, modernization_summary_ui_modernization_summary, styles_reference_styles_components_reference, ui_modernization_guide_ui_modernization_guide [EXTRACTED 1.00]

## Communities (197 total, 37 thin omitted)

### Community 0 - "Admin Console Controllers"
Cohesion: 0.05
Nodes (21): Attendance, AttendanceLog, AttendanceSetting, self, ClassSessionAttendance, school(), Holiday, SubjectAssignment (+13 more)

### Community 1 - "Enrollment Management"
Cohesion: 0.06
Nodes (12): EnrollmentController, StudentController, StudentController, StudentRequest, Attribute, Student, StudentEnrollment, EnrollmentService (+4 more)

### Community 2 - "School Year Management"
Cohesion: 0.06
Nodes (9): SchoolYearController, SchoolYearRequest, SchoolCalendar, self, SchoolYear, DashboardService, SchoolCalendarService, Illuminate\Database\Eloquent\Relations\HasMany (+1 more)

### Community 3 - "QR ID Cards"
Cohesion: 0.08
Nodes (12): QrCardController, QrCheckinController, AttendanceUnlock, Section, SectionPolicy, AttendanceService, Carbon, Illuminate\Http\Response (+4 more)

### Community 4 - "Grade Levels Admin"
Cohesion: 0.06
Nodes (13): GradeLevelController, GradeLevelRequest, GradeLevel, self, GradeLevelPolicy, AcademicStructureSeeder, AdminUserSeeder, DatabaseSeeder (+5 more)

### Community 5 - "Auth & Sessions"
Cohesion: 0.08
Nodes (15): AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController, RegisteredUserController (+7 more)

### Community 6 - "Audit Logging"
Cohesion: 0.11
Nodes (7): AuditLogController, BillingController, AttendanceController, ClassScanController, Sf2Controller, Illuminate\Http\JsonResponse, Illuminate\Http\Request

### Community 7 - "Dashboard & SF2 Reporting"
Cohesion: 0.09
Nodes (10): DashboardController, CuttingClassController, TeacherDashboardController, CuttingClassService, Carbon, Carbon, SalesService, Carbon (+2 more)

### Community 8 - "Frontend NPM Toolchain"
Cohesion: 0.06
Nodes (33): alpinejs, @alpinejs/collapse, @alpinejs/intersect, autoprefixer, axios, concurrently, laravel-vite-plugin, devDependencies (+25 more)

### Community 9 - "Schools Admin"
Cohesion: 0.12
Nodes (5): SchoolController, SchoolRequest, School, RegistrationTest, SchoolScopingTest

### Community 10 - "Database Factories"
Cohesion: 0.09
Nodes (12): GradeLevelFactory, static, SchoolFactory, static, SchoolYearFactory, SectionFactory, StudentFactory, SubjectFactory (+4 more)

### Community 11 - "Class Sessions (Start Class)"
Cohesion: 0.11
Nodes (3): ClassSessionController, ClassSession, ClassSessionTest

### Community 12 - "Subjects Admin"
Cohesion: 0.12
Nodes (4): SubjectController, SubjectRequest, Subject, SubjectPolicy

### Community 13 - "UI Modernization Docs"
Cohesion: 0.12
Nodes (25): Alpine x-data Multi-Modal State Pattern, Shared form-inline.blade.php Partial Pattern, x-icon-btn Component, Modal Conversion Pattern (inline CRUD modals), Modal + Icon Conversion Guide, x-admin-layout Component, x-badge Component, x-button Component (+17 more)

### Community 14 - "Teacher Subject Assignment"
Cohesion: 0.12
Nodes (6): AssignmentController, VerifyEmailController, ProfileController, SubjectController, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse

### Community 15 - "Scan Portal (Legacy)"
Cohesion: 0.14
Nodes (5): ScanPortalController, TeacherScheduleController, Attribute, Carbon, TeacherSchedule

### Community 16 - "User Model & Roles"
Cohesion: 0.13
Nodes (5): User, SchoolYearPolicy, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, ProfileTest

### Community 17 - "Teacher Search & Model"
Cohesion: 0.12
Nodes (4): SearchController, Attribute, Teacher, TeacherScheduleTest

### Community 20 - "Composer Autoload Dev"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

### Community 21 - "Ref Php Artisan"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 22 - "App Http Requests"
Cohesion: 0.18
Nodes (3): ProfileUpdateRequest, SectionRequest, Illuminate\Foundation\Http\FormRequest

### Community 26 - "App Http Middleware"
Cohesion: 0.32
Nodes (5): EnsureActiveSubscription, EnsureUserIsAdmin, EnsureUserIsTeacher, Closure, Symfony\Component\HttpFoundation\Response

### Community 27 - "App Exports Sf2Export"
Cohesion: 0.29
Nodes (5): Sf2Export, Illuminate\Contracts\View\View, Maatwebsite\Excel\Concerns\FromView, Maatwebsite\Excel\Concerns\WithEvents, Maatwebsite\Excel\Concerns\WithTitle

### Community 28 - "App Exports Studentsexport"
Cohesion: 0.29
Nodes (4): StudentsExport, Maatwebsite\Excel\Concerns\FromQuery, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping

### Community 30 - "App Imports Studentsimport"
Cohesion: 0.36
Nodes (5): StudentsImport, Maatwebsite\Excel\Concerns\SkipsEmptyRows, Maatwebsite\Excel\Concerns\ToModel, Maatwebsite\Excel\Concerns\WithHeadingRow, Maatwebsite\Excel\Concerns\WithValidation

### Community 31 - "Composer Require Dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision (+1 more)

### Community 36 - "Ref Php Artisan"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 37 - "Resources Js App"
Cohesion: 0.32
Nodes (3): init(), openCreate(), openEdit()

### Community 44 - "Composer Allow Plugins"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 45 - "Composer Require Laravel"
Cohesion: 0.29
Nodes (7): require, barryvdh/laravel-dompdf, endroid/qr-code, laravel/framework, laravel/tinker, maatwebsite/excel, php

### Community 48 - "Public Logo Blue"
Cohesion: 0.47
Nodes (6): Blue and White Scalloped-Seal Brand Identity, Dahile Provincial Community High School, Emblem Imagery: Open Book, Torch of Knowledge, Skilled Worker, Graduation Cap and Diploma, Founding Year MCMXCVI (1996), Motto: Qualitas Educationis (Quality of Education), Dahile Provincial Community High School Seal

### Community 50 - "Composer Psr 4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 51 - "Public Eaj Primary"
Cohesion: 0.60
Nodes (5): EAJ Web Development Services Brand, Navy, White, and Pink-Red Brand Palette, EA Monogram Mark, EAJ Web Development Services Primary Logo, School Attendance Application

### Community 54 - "Ref Php Artisan"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 56 - "Profile Partials Update"
Cohesion: 0.50
Nodes (3): profile.partials.delete-user-form, profile.partials.update-password-form, profile.partials.update-profile-information-form

### Community 57 - "Public Eaj Appicon"
Cohesion: 0.67
Nodes (4): Icon Design: Navy Squircle with Pink/White EA Monogram, EAJ Systems Brand Identity, EAJ App Icon (EA Monogram), School Attendance Application (DepEd SF2)

### Community 60 - "Composer Extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **95 isolated node(s):** `admin.items.form`, `$schema`, `name`, `type`, `description` (+90 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **37 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Model & Roles` to `Admin Console Controllers`, `Enrollment Management`, `School Year Management`, `QR ID Cards`, `Grade Levels Admin`, `Auth & Sessions`, `Dashboard & SF2 Reporting`, `Schools Admin`, `Class Sessions (Start Class)`, `Subjects Admin`, `Teacher Search & Model`, `App Models Setting`, `App Http Controllers`, `Tests Feature Attendancetest`, `App Http Controllers`, `Tests Feature Adminaccesstest`, `Tests Feature Sf2Reporttest`, `App Http Controllers`, `App Services Paymongoservice`, `App Models User`, `Tests Feature Subscriptiontest`, `App Http Controllers`, `App Policies Studentpolicy`, `App Policies Teacherpolicy`, `App Providers Appserviceprovider`, `Tests Feature Auth`, `Tests Feature Auth`, `Tests Feature Auth`, `Tests Feature Auth`?**
  _High betweenness centrality (0.108) - this node is a cross-community bridge._
- **Why does `SchoolYear` connect `School Year Management` to `Admin Console Controllers`, `Enrollment Management`, `App Http Controllers`, `QR ID Cards`, `Grade Levels Admin`, `Tests Feature Sf2Reporttest`, `Dashboard & SF2 Reporting`, `App Http Controllers`, `Database Factories`, `Class Sessions (Start Class)`, `Scan Portal (Legacy)`, `User Model & Roles`, `App Providers Appserviceprovider`, `Teacher Search & Model`, `Tests Feature Attendancetest`, `App Services Auditlogger`, `Tests Feature Adminaccesstest`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Why does `Section` connect `QR ID Cards` to `Admin Console Controllers`, `Enrollment Management`, `App Http Controllers`, `School Year Management`, `Grade Levels Admin`, `Tests Feature Sf2Reporttest`, `Audit Logging`, `Dashboard & SF2 Reporting`, `App Http Controllers`, `Class Sessions (Start Class)`, `Teacher Subject Assignment`, `User Model & Roles`, `Teacher Search & Model`, `App Http Requests`, `Tests Feature Attendancetest`, `App Services Auditlogger`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Are the 35 inferred relationships involving `User` (e.g. with `.store()` and `.update()`) actually correct?**
  _`User` has 35 INFERRED edges - model-reasoned connections that need verification._
- **Are the 15 inferred relationships involving `Section` (e.g. with `.index()` and `.store()`) actually correct?**
  _`Section` has 15 INFERRED edges - model-reasoned connections that need verification._
- **Are the 32 inferred relationships involving `SchoolYear` (e.g. with `.__invoke()` and `.index()`) actually correct?**
  _`SchoolYear` has 32 INFERRED edges - model-reasoned connections that need verification._
- **What connects `admin.items.form`, `$schema`, `name` to the rest of the system?**
  _95 weakly-connected nodes found - possible documentation gaps or missing edges._