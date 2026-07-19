# Graph Report - d:\EAJ SYSTEMS\sf2  (2026-07-19)

## Corpus Check
- 120 files · ~334,366 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1703 nodes · 3903 edges · 262 communities (203 shown, 59 thin omitted)
- Extraction: 92% EXTRACTED · 8% INFERRED · 0% AMBIGUOUS · INFERRED: 315 edges (avg confidence: 0.81)
- Token cost: 121,084 input · 0 output

## Community Hubs (Navigation)
- Auth & Teacher Assignment
- Attendance Model & Dashboard
- Audit Log & Billing
- User Model & Policies
- Subject Management
- School Management
- Deployment & Webhook Security
- Scan Portal & Teacher Schedule
- Test Data Factories
- Subscription Plan Catalog
- Student Model & QR Tokens
- Class Session Board
- Settings & PayMongo Credentials
- Core Domain Models
- Teacher Registration Approval
- Attendance Locking Rules
- Frontend NPM Dependencies
- School Year Lifecycle
- Grade Level Management
- SF3 Books Report
- Enrollment Transfers
- Textbook Issuance
- Subject Assignment
- Auth Feature Tests
- Subscription Payments & Upgrades
- QR Check-in
- School ID Verification Tests
- Promotion Management
- SF1 School Register
- Landing Page & Pricing
- Student Admin CRUD
- Subscription Access Control
- Blade UI Component Library
- Student Import/Export
- Route Middleware Guards
- Form Request Validation
- Teacher Schedule Tests
- Section Management
- Cutting Class & Teacher Dashboard
- Composer Manifest
- Composer Scripts
- SF5 Report Tests
- Student Controller
- Teacher Management
- SF2 Report Service
- Illuminate\Database\Seeder
- AttendanceTest
- NotificationBellTest
- Sf1ReportTest
- InsightsService
- Textbook
- SchoolCalendarService
- InsightsTest
- SubscriptionAuditTrailTest
- Sf5ReportService
- app.js
- Sf2Export
- QrCardController
- Section.php
- UI Modernization Guide
- AdminAccessTest
- AttendanceServiceTest
- StudentsImport
- AuditLog.php
- require-dev
- EnrollmentTest
- ModuleEntitlementTest
- Sf2ReportTest
- SubscriptionTest
- SettingsController
- setup
- Controller
- LoginRequest
- AttendanceSetting
- GradeLevelPolicy
- config
- require
- package.json
- ProfileTest
- SubscribePageStatesTest
- DashboardService
- NotificationService
- DepEd Logo (Department of Education Phil
- Dahile Provincial Community High School 
- SchoolCalendarServiceTest
- psr-4
- EAJ Web Development Services Primary Log
- Dahile Provincial Community High School 
- AuthenticationTest
- PasswordResetTest
- Illuminate\Database\Eloquent\Relations\H
- AppServiceProvider
- post-create-project-cmd
- ExampleTest
- edit.blade.php
- EAJ App Icon (EA Monogram)
- Dahile Provincial Community High School 
- PasswordConfirmationTest
- extra
- app.blade.php
- admin-layout.blade.php
- app-shell.blade.php
- admin.items.form
- admin.students.form-inline
- autoprefixer
- laravel-vite-plugin
- create.blade.php
- edit.blade.php
- login.blade.php
- guest.blade.php
- excel.blade.php
- print.blade.php
- self
- Attribute
- Robots.txt Allow-All Crawl Policy

## God Nodes (most connected - your core abstractions)
1. `User` - 207 edges
2. `SchoolYear` - 110 edges
3. `StudentEnrollment` - 80 edges
4. `AuditLogger` - 78 edges
5. `Student` - 72 edges
6. `Section` - 71 edges
7. `Controller` - 53 edges
8. `School` - 42 edges
9. `GradeLevel` - 38 edges
10. `Setting` - 35 edges

## Surprising Connections (you probably didn't know these)
- `Modal + Icon Conversion Guide` --conceptually_related_to--> `UI Modernization Checklist`  [INFERRED]
  MODAL_CONVERSION_GUIDE.md → MODERNIZATION_CHECKLIST.md
- `Modal Conversion Pattern (inline CRUD modals)` --semantically_similar_to--> `Modal-Based Forms`  [INFERRED] [semantically similar]
  MODAL_CONVERSION_GUIDE.md → UI_MODERNIZATION_GUIDE.md
- `x-icon-btn Component` --semantically_similar_to--> `Icon-Based Actions (view/edit/delete)`  [INFERRED] [semantically similar]
  MODAL_CONVERSION_GUIDE.md → UI_MODERNIZATION_GUIDE.md
- `Mobile-First Responsive Design` --conceptually_related_to--> `tailwindcss`  [INFERRED]
  MODERNIZATION_SUMMARY.md → package.json
- `Styles & Components Reference` --conceptually_related_to--> `tailwindcss`  [INFERRED]
  STYLES_REFERENCE.md → package.json

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **PayMongo Webhook Subscription Activation Flow** — deployment_subscription_webhook_endpoint, deployment_paymongoservice_verifywebhooksignature, deployment_paymongo_webhook_secret, deployment_subscription_payments_table, readme_subscription_billing [EXTRACTED 1.00]
- **School Calendar Generation Gates Attendance** — deployment_schoolyear, deployment_schoolcalendar, deployment_schoolcalendarservice, deployment_missing_calendar_row_locks_attendance, readme_sf2 [INFERRED 0.85]
- **Attendance Capture to DepEd Form Output Pipeline** — readme_qr_checkin, readme_manual_marking_grid, readme_premark_absent, readme_sf2_pdf_export, readme_dompdf [INFERRED 0.85]
- **Reusable Blade Component Library** — modernization_checklist_button_component, modernization_checklist_badge_component, modernization_checklist_delete_confirm_btn, modernization_checklist_card_component, modernization_checklist_admin_layout_component [EXTRACTED 1.00]
- **Modern CRUD Modal UX Flow** — ui_modernization_guide_modal_forms, ui_modernization_guide_alpine_js, ui_modernization_guide_icon_based_actions, modernization_summary_sweetalert2 [INFERRED 0.75]
- **UI Modernization Documentation Suite** — modal_conversion_guide_modal_icon_conversion_guide, modernization_checklist_ui_modernization_checklist, modernization_summary_ui_modernization_summary, styles_reference_styles_components_reference, ui_modernization_guide_ui_modernization_guide [EXTRACTED 1.00]

## Communities (262 total, 59 thin omitted)

### Community 0 - "Auth & Teacher Assignment"
Cohesion: 0.08
Nodes (19): AssignmentController, AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController (+11 more)

### Community 1 - "Attendance Model & Dashboard"
Cohesion: 0.09
Nodes (10): Attendance, AttendanceLog, AttendanceUnlock, ClassSessionAttendance, school(), Holiday, TeacherSubjectAssignment, Illuminate\Database\Eloquent\Factories\HasFactory (+2 more)

### Community 2 - "Audit Log & Billing"
Cohesion: 0.09
Nodes (8): AuditLogController, BillingController, AttendanceController, ClassScanController, Sf5Controller, SubscriptionController, Illuminate\Http\JsonResponse, Illuminate\Http\Request

### Community 3 - "User Model & Policies"
Cohesion: 0.08
Nodes (7): User, SchoolYearPolicy, SectionPolicy, TeacherPolicy, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, EmailVerificationTest

### Community 4 - "Subject Management"
Cohesion: 0.09
Nodes (6): SearchController, SubjectController, SubjectController, SubjectRequest, Subject, SubjectPolicy

### Community 5 - "School Management"
Cohesion: 0.10
Nodes (5): SchoolController, SchoolRequest, School, RegistrationTest, SchoolScopingTest

### Community 6 - "Deployment & Webhook Security"
Cohesion: 0.07
Nodes (31): APP_DEBUG Secret Leak Risk, Admin Override Preservation (is_override), Stale Config Cache Hazard, Deployment Checklist, Fail-Closed HMAC Signature Verification, Mail Transport Configuration, Missing Calendar Row Locks Attendance, PayMongo Webhook Secret (PAYMONGO_WEBHOOK_SECRET) (+23 more)

### Community 7 - "Scan Portal & Teacher Schedule"
Cohesion: 0.11
Nodes (6): ScanPortalController, TeacherScheduleController, Attribute, Carbon, TeacherSchedule, Illuminate\Database\Eloquent\Builder

### Community 8 - "Test Data Factories"
Cohesion: 0.09
Nodes (12): GradeLevelFactory, static, SchoolFactory, static, SchoolYearFactory, SectionFactory, StudentFactory, SubjectFactory (+4 more)

### Community 9 - "Subscription Plan Catalog"
Cohesion: 0.11
Nodes (3): SubscriptionPlans, self, SubscriptionPlanTest

### Community 10 - "Student Model & QR Tokens"
Cohesion: 0.12
Nodes (5): Carbon, Student, StudentPolicy, Attribute, Phase6Test

### Community 11 - "Class Session Board"
Cohesion: 0.11
Nodes (3): ClassSessionController, ClassSession, ClassSessionTest

### Community 12 - "Settings & PayMongo Credentials"
Cohesion: 0.13
Nodes (3): Setting, PayMongoService, SubscriptionWebhookTest

### Community 14 - "Teacher Registration Approval"
Cohesion: 0.13
Nodes (5): RegistrationController, SchoolIdDocumentController, SectionController, AuditLogger, Symfony\Component\HttpFoundation\StreamedResponse

### Community 15 - "Attendance Locking Rules"
Cohesion: 0.20
Nodes (6): AttendanceService, Carbon, SchoolCalendarService, Attendance, AttendanceSetting, Illuminate\Support\Carbon

### Community 16 - "Frontend NPM Dependencies"
Cohesion: 0.09
Nodes (23): alpinejs, @alpinejs/collapse, @alpinejs/intersect, axios, concurrently, jsqr, devDependencies, alpinejs (+15 more)

### Community 18 - "Grade Level Management"
Cohesion: 0.14
Nodes (4): GradeLevelController, GradeLevelRequest, GradeLevel, self

### Community 19 - "SF3 Books Report"
Cohesion: 0.15
Nodes (6): DashboardController, Sf3Controller, Carbon, SalesService, Sf3ReportService, Illuminate\Support\Collection

### Community 20 - "Enrollment Transfers"
Cohesion: 0.15
Nodes (3): EnrollmentController, StudentEnrollment, EnrollmentService

### Community 21 - "Textbook Issuance"
Cohesion: 0.24
Nodes (3): TextbookIssuance, Section, Sf3ReportTest

### Community 22 - "Subject Assignment"
Cohesion: 0.12
Nodes (5): SubjectAssignment, Attribute, Teacher, DemoDataSeeder, Illuminate\Database\Eloquent\Relations\HasMany

### Community 23 - "Auth Feature Tests"
Cohesion: 0.22
Nodes (4): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, PasswordUpdateTest, TestCase

### Community 25 - "QR Check-in"
Cohesion: 0.16
Nodes (3): QrCheckinController, Section, PromotionServiceTest

### Community 27 - "Promotion Management"
Cohesion: 0.15
Nodes (4): PromotionController, PromotionController, Section, PromotionService

### Community 28 - "SF1 School Register"
Cohesion: 0.18
Nodes (3): Sf1Controller, Carbon, Sf1ReportService

### Community 30 - "Student Admin CRUD"
Cohesion: 0.17
Nodes (3): StudentController, StudentRequest, Illuminate\Contracts\Validation\Validator

### Community 32 - "Blade UI Component Library"
Cohesion: 0.17
Nodes (16): x-admin-layout Component, x-badge Component, x-button Component, x-card Component, x-delete-confirm-btn Component, UI Modernization Checklist, Indigo/Emerald/Red Color Scheme, Dark Mode Support (+8 more)

### Community 33 - "Student Import/Export"
Cohesion: 0.17
Nodes (5): StudentsExport, StudentIoController, Maatwebsite\Excel\Concerns\FromQuery, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping

### Community 34 - "Route Middleware Guards"
Cohesion: 0.27
Nodes (6): EnsureActiveSubscription, EnsureModuleAccess, EnsureUserIsAdmin, EnsureUserIsTeacher, Closure, Symfony\Component\HttpFoundation\Response

### Community 35 - "Form Request Validation"
Cohesion: 0.14
Nodes (5): ProfileUpdateRequest, SchoolYearRequest, TeacherRegistrationRequest, TeacherRequest, Illuminate\Foundation\Http\FormRequest

### Community 36 - "Teacher Schedule Tests"
Cohesion: 0.17
Nodes (3): Section, Teacher, TeacherScheduleTest

### Community 38 - "Cutting Class & Teacher Dashboard"
Cohesion: 0.20
Nodes (4): CuttingClassController, TeacherDashboardController, CuttingClassService, Carbon

### Community 39 - "Composer Manifest"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

### Community 40 - "Composer Scripts"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 45 - "Illuminate\Database\Seeder"
Cohesion: 0.23
Nodes (7): AdminUserSeeder, DatabaseSeeder, JadeTeacherSeeder, SchoolSeeder, SecondYearSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 55 - "app.js"
Cohesion: 0.22
Nodes (3): init(), openCreate(), openEdit()

### Community 56 - "Sf2Export"
Cohesion: 0.29
Nodes (5): Sf2Export, Illuminate\Contracts\View\View, Maatwebsite\Excel\Concerns\FromView, Maatwebsite\Excel\Concerns\WithEvents, Maatwebsite\Excel\Concerns\WithTitle

### Community 57 - "QrCardController"
Cohesion: 0.36
Nodes (3): QrCardController, Illuminate\Http\Response, Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 59 - "UI Modernization Guide"
Cohesion: 0.27
Nodes (10): Alpine x-data Multi-Modal State Pattern, Shared form-inline.blade.php Partial Pattern, x-icon-btn Component, Modal Conversion Pattern (inline CRUD modals), Modal + Icon Conversion Guide, Modal Markup Pattern (fade + scale, backdrop, ESC close), Alpine.js, Icon-Based Actions (view/edit/delete) (+2 more)

### Community 62 - "StudentsImport"
Cohesion: 0.36
Nodes (5): StudentsImport, Maatwebsite\Excel\Concerns\SkipsEmptyRows, Maatwebsite\Excel\Concerns\ToModel, Maatwebsite\Excel\Concerns\WithHeadingRow, Maatwebsite\Excel\Concerns\WithValidation

### Community 64 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision (+1 more)

### Community 71 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 74 - "AttendanceSetting"
Cohesion: 0.29
Nodes (3): AttendanceSetting, self, AcademicStructureSeeder

### Community 76 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 77 - "require"
Cohesion: 0.29
Nodes (7): require, barryvdh/laravel-dompdf, endroid/qr-code, laravel/framework, laravel/tinker, maatwebsite/excel, php

### Community 78 - "package.json"
Cohesion: 0.29
Nodes (6): private, $schema, scripts, build, dev, type

### Community 83 - "DepEd Logo (Department of Education Phil"
Cohesion: 0.53
Nodes (6): DepEd Logo (Department of Education Philippines), Blue/Red/Gold Institutional Palette, Department of Education (Philippines), Official Report Branding Asset (SF1/SF2 Forms), Torch Emblem with Red Flame, DepED Wordmark and 'Department of Education' Text

### Community 84 - "Dahile Provincial Community High School "
Cohesion: 0.47
Nodes (6): Blue and White Scalloped-Seal Brand Identity, Dahile Provincial Community High School, Emblem Imagery: Open Book, Torch of Knowledge, Skilled Worker, Graduation Cap and Diploma, Founding Year MCMXCVI (1996), Motto: Qualitas Educationis (Quality of Education), Dahile Provincial Community High School Seal

### Community 86 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 87 - "EAJ Web Development Services Primary Log"
Cohesion: 0.60
Nodes (5): EAJ Web Development Services Brand, Navy, White, and Pink-Red Brand Palette, EA Monogram Mark, EAJ Web Development Services Primary Logo, School Attendance Application

### Community 88 - "Dahile Provincial Community High School "
Cohesion: 0.50
Nodes (5): Dahile Provincial Community High School Seal (uploaded logo), Motto: Qualitas Educationis, SF1/SF2 report header branding asset, School Identity: Dahile Provincial Community High School, est. MCMXCVI (1996), School logo upload storage (public/school-logos, hashed filename)

### Community 93 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 95 - "edit.blade.php"
Cohesion: 0.50
Nodes (3): profile.partials.delete-user-form, profile.partials.update-password-form, profile.partials.update-profile-information-form

### Community 96 - "EAJ App Icon (EA Monogram)"
Cohesion: 0.67
Nodes (4): Icon Design: Navy Squircle with Pink/White EA Monogram, EAJ Systems Brand Identity, EAJ App Icon (EA Monogram), School Attendance Application (DepEd SF2)

### Community 97 - "Dahile Provincial Community High School "
Cohesion: 0.67
Nodes (4): Dahile Provincial Community High School Seal (uploaded logo), SF1/SF2 Report Header Branding, Dahile Provincial Community High School (est. MCMXCVI / 1996), School Logo Upload Storage (hashed-filename public asset)

### Community 99 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Ambiguous Edges - Review These
- `Blue/Red/Gold Institutional Palette` → `Official Report Branding Asset (SF1/SF2 Forms)`  [AMBIGUOUS]
  public/DepED-Logo.png · relation: conceptually_related_to
- `Dahile Provincial Community High School (est. MCMXCVI / 1996)` → `SF1/SF2 Report Header Branding`  [AMBIGUOUS]
  public/school-logos/7HMm7QbMuNc4oRws04KgIpinbYCHF19kIVHXCiKR.png · relation: shares_data_with
- `School Identity: Dahile Provincial Community High School, est. MCMXCVI (1996)` → `SF1/SF2 report header branding asset`  [AMBIGUOUS]
  public/school-logos/hmaKxU3RX0aSdYty3rall9Rr35zmFFBTCDKquXjP.png · relation: shares_data_with

## Knowledge Gaps
- **101 isolated node(s):** `admin.items.form`, `$schema`, `name`, `type`, `description` (+96 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **59 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **What is the exact relationship between `Blue/Red/Gold Institutional Palette` and `Official Report Branding Asset (SF1/SF2 Forms)`?**
  _Edge tagged AMBIGUOUS (relation: conceptually_related_to) - confidence is low._
- **What is the exact relationship between `Dahile Provincial Community High School (est. MCMXCVI / 1996)` and `SF1/SF2 Report Header Branding`?**
  _Edge tagged AMBIGUOUS (relation: shares_data_with) - confidence is low._
- **What is the exact relationship between `School Identity: Dahile Provincial Community High School, est. MCMXCVI (1996)` and `SF1/SF2 report header branding asset`?**
  _Edge tagged AMBIGUOUS (relation: shares_data_with) - confidence is low._
- **Why does `User` connect `User Model & Policies` to `Auth & Teacher Assignment`, `Attendance Model & Dashboard`, `Subject Management`, `School Management`, `Subscription Plan Catalog`, `Student Model & QR Tokens`, `Class Session Board`, `Settings & PayMongo Credentials`, `Core Domain Models`, `Teacher Registration Approval`, `Attendance Locking Rules`, `SF3 Books Report`, `Textbook Issuance`, `Subject Assignment`, `Auth Feature Tests`, `Subscription Payments & Upgrades`, `QR Check-in`, `School ID Verification Tests`, `Promotion Management`, `Landing Page & Pricing`, `Student Admin CRUD`, `Subscription Access Control`, `Teacher Schedule Tests`, `Cutting Class & Teacher Dashboard`, `SF5 Report Tests`, `Teacher Management`, `AttendanceTest`, `NotificationBellTest`, `Sf1ReportTest`, `InsightsService`, `InsightsTest`, `SubscriptionAuditTrailTest`, `AdminAccessTest`, `AttendanceServiceTest`, `EnrollmentTest`, `ModuleEntitlementTest`, `Sf2ReportTest`, `SubscriptionTest`, `SchoolCalendarService.php`, `GradeLevelPolicy`, `ProfileTest`, `SubscribePageStatesTest`, `NotificationService`, `AuthenticationTest`, `PasswordResetTest`, `Illuminate\Database\Eloquent\Relations\H`, `AppServiceProvider`, `PasswordConfirmationTest`?**
  _High betweenness centrality (0.145) - this node is a cross-community bridge._
- **Why does `SchoolYear` connect `School Year Lifecycle` to `Attendance Model & Dashboard`, `User Model & Policies`, `School Management`, `Scan Portal & Teacher Schedule`, `Test Data Factories`, `Subscription Plan Catalog`, `Student Model & QR Tokens`, `Class Session Board`, `Core Domain Models`, `Teacher Registration Approval`, `Attendance Locking Rules`, `SF3 Books Report`, `Enrollment Transfers`, `Textbook Issuance`, `Subject Assignment`, `Auth Feature Tests`, `QR Check-in`, `Promotion Management`, `Student Admin CRUD`, `Teacher Schedule Tests`, `Section Management`, `Cutting Class & Teacher Dashboard`, `SF5 Report Tests`, `AttendanceTest`, `Sf1ReportTest`, `SchoolCalendarService`, `InsightsTest`, `AdminAccessTest`, `AttendanceServiceTest`, `EnrollmentTest`, `Sf2ReportTest`, `SchoolCalendarService.php`, `AttendanceSetting`, `DashboardService`, `SchoolCalendarServiceTest`, `Illuminate\Database\Eloquent\Relations\H`, `AppServiceProvider`?**
  _High betweenness centrality (0.064) - this node is a cross-community bridge._
- **Why does `Student` connect `Student Model & QR Tokens` to `Attendance Model & Dashboard`, `Audit Log & Billing`, `Subject Management`, `School Management`, `Class Session Board`, `Core Domain Models`, `Attendance Locking Rules`, `Enrollment Transfers`, `Textbook Issuance`, `Subject Assignment`, `QR Check-in`, `Student Admin CRUD`, `Student Import/Export`, `Teacher Schedule Tests`, `SF5 Report Tests`, `Student Controller`, `AttendanceTest`, `Sf1ReportTest`, `InsightsTest`, `QrCardController`, `StudentsImport`, `EnrollmentTest`, `Sf2ReportTest`, `Illuminate\Database\Eloquent\Relations\H`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 39 inferred relationships involving `User` (e.g. with `.store()` and `.update()`) actually correct?**
  _`User` has 39 INFERRED edges - model-reasoned connections that need verification._