# Graph Report - .  (2026-07-20)

## Corpus Check
- 62 files · ~351,328 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1888 nodes · 4407 edges · 275 communities (210 shown, 65 thin omitted)
- Extraction: 92% EXTRACTED · 8% INFERRED · 0% AMBIGUOUS · INFERRED: 362 edges (avg confidence: 0.81)
- Token cost: 39,132 input · 0 output

## Community Hubs (Navigation)
- Subscription Plans & Pricing
- Learner Promotion Flow
- Admin Audit & Billing
- Payments & PayMongo Billing
- User Roles & Policies
- Auth & Email Verification
- Subject Management
- QR Scan Portal & Schedules
- Attendance Records
- Deployment Hardening Notes
- Student Model & School Year
- Test Factories & Seeding
- Admin Section & Search
- Teacher Registration Approval
- User Registration & Profile
- Class Session Board
- Attendance Logs & Unlocks
- Attendance Service Rules
- Admin Dashboard & Payments
- Frontend Build Tooling
- App Services Paymongoservice
- Tests Feature Schoolscopingtest
- App Models Schoolyear
- App Http Controllers
- Tests Feature Sf3Reporttest
- App Http Controllers
- App Http Controllers
- App Models User
- App Policies Sectionpolicy
- App Models Teacher
- Graphify Out Converted
- Tests Feature Schoolidverificationtest
- Tests Feature Teacherscheduletest
- Modernization Checklist Admin
- App Exports Studentsexport
- App Http Middleware
- Tests Feature Subscriptionsettlementtest
- App Http Controllers
- App Http Controllers
- App Services Sf3Reportservice
- App Models Gradelevel
- Tests Feature Reportschooltest
- Composer Autoload Dev
- Ref Php Artisan
- Tests Feature Sf1Reporttest
- Tests Feature Sf5Reporttest
- Tests Feature Subscriptionproductionread
- App Http Controllers
- App Services Sf2Reportservice
- Tests Feature Notificationbelltest
- App Console Commands
- App Http Controllers
- App Http Requests
- App Services Insightsservice
- Database Seeders Databaseseeder
- App Http Controllers
- App Http Controllers
- App Models Holiday
- App Models Setting
- App Services Sf1Reportservice
- App Services Sf5Reportservice
- Resources Js App
- App Exports Sf2Export
- App Http Controllers
- App Http Controllers
- App Http Controllers
- Modal Conversion Guide
- Tests Feature Insightstest
- Tests Unit Attendanceservicetest
- App Http Controllers
- App Imports Studentsimport
- Composer Require Dev
- Tests Feature Enrollmenttest
- Tests Feature Sf2Reporttest
- Tests Feature Subscriptiontest
- App Http Controllers
- App Services Notificationservice
- Ref Php Artisan
- Tests Feature Settingcachingtest
- Tests Feature Subscriptionwebhooktest
- Tests Feature Tenantisolationtest
- Tests Feature Trialcountdowntest
- App Http Controllers
- App Models Attendancesetting
- Composer Allow Plugins
- Composer Require Laravel
- Package
- Tests Feature Subscribepagestatestest
- App Http Controllers
- App Providers Appserviceprovider
- Public Deped Logo
- Public Logo Blue
- Tests Feature Compedaccountstatustest
- App Http Controllers
- Composer Psr 4
- Public Eaj Primary
- Public School Logos
- Tests Feature Landingsmoketest
- App Http Requests
- Ref Php Artisan
- Database Factories Sectionfactory
- Database Migrations 2026
- Tests Unit Exampletest
- Profile Partials Update
- Public Eaj Appicon
- Public School Logos
- Composer Extra
- Database Seeders Adminuserseeder
- Resources Views Layouts
- Partials Confirm Delete
- Resources Views Components
- Admin Items Form
- Admin Students Form
- Laravel Vite Plugin
- Package Devdependencies Sweetalert2
- Resources Views Admin
- Resources Views Admin
- Resources Views Auth
- Resources Views Layouts
- Resources Views Reports
- Resources Views Reports
- App Http Controllers
- App Models Schoolyear
- App Models Student
- Database Factories Userfactory
- Public Robots Allow

## God Nodes (most connected - your core abstractions)
1. `User` - 240 edges
2. `SchoolYear` - 104 edges
3. `Section` - 71 edges
4. `Student` - 69 edges
5. `AuditLogger` - 69 edges
6. `StudentEnrollment` - 68 edges
7. `School` - 68 edges
8. `TestCase` - 58 edges
9. `Setting` - 52 edges
10. `SubscriptionPlans` - 51 edges

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
- **DepEd School Forms Reporting System (SF1, SF3, SF5 per class section)** — graphify_out_converted_sf1_report_final_form_0dd1b419_school_form_1_school_register, graphify_out_converted_sf3_report_final_form_21690f41_school_form_3_books_issued_and_returned, graphify_out_converted_sf5_report_final_form_501c675f_school_form_5_promotion_report [INFERRED 0.85]
- **SF5 Prepare-Certify-Review Workflow** — graphify_out_converted_sf5_report_final_form_501c675f_class_adviser, graphify_out_converted_sf5_report_final_form_501c675f_school_head, graphify_out_converted_sf5_report_final_form_501c675f_division_representative, graphify_out_converted_sf5_report_final_form_501c675f_school_form_5_promotion_report [EXTRACTED 1.00]
- **PayMongo Webhook Subscription Activation Flow** — deployment_subscription_webhook_endpoint, deployment_paymongoservice_verifywebhooksignature, deployment_paymongo_webhook_secret, deployment_subscription_payments_table, readme_subscription_billing [EXTRACTED 1.00]
- **School Calendar Generation Gates Attendance** — deployment_schoolyear, deployment_schoolcalendar, deployment_schoolcalendarservice, deployment_missing_calendar_row_locks_attendance, readme_sf2 [INFERRED 0.85]
- **Attendance Capture to DepEd Form Output Pipeline** — readme_qr_checkin, readme_manual_marking_grid, readme_premark_absent, readme_sf2_pdf_export, readme_dompdf [INFERRED 0.85]
- **Reusable Blade Component Library** — modernization_checklist_button_component, modernization_checklist_badge_component, modernization_checklist_delete_confirm_btn, modernization_checklist_card_component, modernization_checklist_admin_layout_component [EXTRACTED 1.00]
- **Modern CRUD Modal UX Flow** — ui_modernization_guide_modal_forms, ui_modernization_guide_alpine_js, ui_modernization_guide_icon_based_actions, modernization_summary_sweetalert2 [INFERRED 0.75]
- **UI Modernization Documentation Suite** — modal_conversion_guide_modal_icon_conversion_guide, modernization_checklist_ui_modernization_checklist, modernization_summary_ui_modernization_summary, styles_reference_styles_components_reference, ui_modernization_guide_ui_modernization_guide [EXTRACTED 1.00]

## Communities (275 total, 65 thin omitted)

### Community 0 - "Subscription Plans & Pricing"
Cohesion: 0.05
Nodes (7): SubscriptionPlans, ModuleEntitlementTest, PlanPricingTest, SubscriptionPlanTest, SubscriptionPayment, SubscriptionUpgradeTest, UpgradeProrationTest

### Community 1 - "Learner Promotion Flow"
Cohesion: 0.05
Nodes (13): PromotionController, PromotionController, SchoolCalendar, SchoolYear, DashboardService, Section, PromotionService, SchoolCalendarService (+5 more)

### Community 2 - "Admin Audit & Billing"
Cohesion: 0.10
Nodes (8): AuditLogController, BillingController, AttendanceController, ClassScanController, QrCheckinController, Sf5Controller, Illuminate\Http\JsonResponse, Illuminate\Http\Request

### Community 3 - "Payments & PayMongo Billing"
Cohesion: 0.14
Nodes (4): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, PasswordUpdateTest, TestCase

### Community 4 - "User Roles & Policies"
Cohesion: 0.08
Nodes (9): User, SchoolYearPolicy, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, AuthenticationTest, EmailVerificationTest, PasswordConfirmationTest, PasswordResetTest (+1 more)

### Community 5 - "Auth & Email Verification"
Cohesion: 0.09
Nodes (15): ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController, VerifyEmailController, Controller (+7 more)

### Community 6 - "Subject Management"
Cohesion: 0.10
Nodes (5): SubjectController, SubjectController, SubjectRequest, Subject, SubjectPolicy

### Community 7 - "QR Scan Portal & Schedules"
Cohesion: 0.09
Nodes (6): ScanPortalController, TeacherScheduleController, Attribute, Carbon, TeacherSchedule, Illuminate\Database\Eloquent\Builder

### Community 8 - "Attendance Records"
Cohesion: 0.10
Nodes (6): Attendance, school(), SubjectAssignment, TeacherSubjectAssignment, DemoDataSeeder, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 9 - "Deployment Hardening Notes"
Cohesion: 0.07
Nodes (31): APP_DEBUG Secret Leak Risk, Admin Override Preservation (is_override), Stale Config Cache Hazard, Deployment Checklist, Fail-Closed HMAC Signature Verification, Mail Transport Configuration, Missing Calendar Row Locks Attendance, PayMongo Webhook Secret (PAYMONGO_WEBHOOK_SECRET) (+23 more)

### Community 10 - "Student Model & School Year"
Cohesion: 0.11
Nodes (6): Carbon, Student, StudentPolicy, Attribute, Illuminate\Database\Eloquent\Relations\HasOne, Phase6Test

### Community 11 - "Test Factories & Seeding"
Cohesion: 0.09
Nodes (10): GradeLevelFactory, static, SchoolFactory, static, SchoolYearFactory, StudentFactory, SubjectFactory, TeacherFactory (+2 more)

### Community 13 - "Teacher Registration Approval"
Cohesion: 0.13
Nodes (5): RegistrationController, SchoolIdDocumentController, SectionController, AuditLogger, Symfony\Component\HttpFoundation\StreamedResponse

### Community 14 - "User Registration & Profile"
Cohesion: 0.09
Nodes (7): RegisteredUserController, ProfileController, ProfileUpdateRequest, SchoolRequest, SchoolYearRequest, TeacherRegistrationRequest, Illuminate\Foundation\Http\FormRequest

### Community 15 - "Class Session Board"
Cohesion: 0.11
Nodes (3): ClassSessionController, ClassSession, ClassSessionTest

### Community 16 - "Attendance Logs & Unlocks"
Cohesion: 0.12
Nodes (7): AttendanceLog, AttendanceUnlock, AuditLog, ClassSessionAttendance, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\MorphTo

### Community 17 - "Attendance Service Rules"
Cohesion: 0.18
Nodes (7): AttendanceService, Carbon, SchoolCalendarService, Carbon, Attendance, AttendanceSetting, Illuminate\Support\Carbon

### Community 18 - "Admin Dashboard & Payments"
Cohesion: 0.14
Nodes (5): DashboardController, SubscriptionPayment, Carbon, SalesService, SubscriptionAuditTrailTest

### Community 19 - "Frontend Build Tooling"
Cohesion: 0.09
Nodes (23): alpinejs, @alpinejs/collapse, @alpinejs/intersect, autoprefixer, axios, concurrently, jsqr, devDependencies (+15 more)

### Community 21 - "Tests Feature Schoolscopingtest"
Cohesion: 0.16
Nodes (4): soleSchoolId(), School, RegistrationTest, SchoolScopingTest

### Community 23 - "App Http Controllers"
Cohesion: 0.13
Nodes (4): StudentController, StudentRequest, QrCodeService, Illuminate\Contracts\Validation\Validator

### Community 24 - "Tests Feature Sf3Reporttest"
Cohesion: 0.24
Nodes (3): TextbookIssuance, Section, Sf3ReportTest

### Community 25 - "App Http Controllers"
Cohesion: 0.19
Nodes (3): AssignmentController, SchoolYearController, Illuminate\Http\RedirectResponse

### Community 26 - "App Http Controllers"
Cohesion: 0.16
Nodes (3): EnrollmentController, StudentEnrollment, EnrollmentService

### Community 28 - "App Policies Sectionpolicy"
Cohesion: 0.17
Nodes (3): Section, SectionPolicy, PromotionServiceTest

### Community 29 - "App Models Teacher"
Cohesion: 0.14
Nodes (4): SearchController, Attribute, Teacher, TeacherPolicy

### Community 30 - "Graphify Out Converted"
Cohesion: 0.16
Nodes (17): Learner Demographic Profile (Sex, Birth Date, Mother Tongue, IP, Religion, Address, Parents, Guardian), Learner Reference Number (LRN), SF1 Remark Indicator Codes (T/O, T/I, DRP, LE, CCT, B/A, LWD, ACL), Replaced Forms: Form 1 Master List & STS Form 2 Family Background and Profile, School Form 1 (SF1) School Register, DepEd Orders DO#23 s.2001, DO#25 s.2003, DO#14 s.2012 (Textbook Accountability), SF3 Lost/Unreturned Book Codes (FM, TDO, NEG, LLTR, TLTR, PTL), School Form 3 (SF3) Books Issued and Returned (+9 more)

### Community 32 - "Tests Feature Teacherscheduletest"
Cohesion: 0.16
Nodes (4): Section, Section, Teacher, TeacherScheduleTest

### Community 33 - "Modernization Checklist Admin"
Cohesion: 0.17
Nodes (16): x-admin-layout Component, x-badge Component, x-button Component, x-card Component, x-delete-confirm-btn Component, UI Modernization Checklist, Indigo/Emerald/Red Color Scheme, Dark Mode Support (+8 more)

### Community 34 - "App Exports Studentsexport"
Cohesion: 0.17
Nodes (5): StudentsExport, StudentIoController, Maatwebsite\Excel\Concerns\FromQuery, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping

### Community 35 - "App Http Middleware"
Cohesion: 0.27
Nodes (6): EnsureActiveSubscription, EnsureModuleAccess, EnsureUserIsAdmin, EnsureUserIsTeacher, Closure, Symfony\Component\HttpFoundation\Response

### Community 39 - "App Services Sf3Reportservice"
Cohesion: 0.24
Nodes (3): Sf3Controller, Sf3ReportService, Illuminate\Support\Collection

### Community 40 - "App Models Gradelevel"
Cohesion: 0.18
Nodes (3): GradeLevel, self, GradeLevelPolicy

### Community 41 - "Tests Feature Reportschooltest"
Cohesion: 0.26
Nodes (4): ReportSchool, Teacher, Section, ReportSchoolTest

### Community 42 - "Composer Autoload Dev"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

### Community 43 - "Ref Php Artisan"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 44 - "Tests Feature Sf1Reporttest"
Cohesion: 0.22
Nodes (3): Section, StudentEnrollment, Sf1ReportTest

### Community 50 - "App Console Commands"
Cohesion: 0.27
Nodes (4): AuditTenancy, SubscriptionPayment, ReconcileSubscriptionPayments, Illuminate\Console\Command

### Community 54 - "Database Seeders Databaseseeder"
Cohesion: 0.24
Nodes (6): DatabaseSeeder, JadeTeacherSeeder, SchoolSeeder, SecondYearSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 59 - "App Services Sf1Reportservice"
Cohesion: 0.31
Nodes (3): Carbon, StudentEnrollment, Sf1ReportService

### Community 61 - "Resources Js App"
Cohesion: 0.22
Nodes (3): init(), openCreate(), openEdit()

### Community 62 - "App Exports Sf2Export"
Cohesion: 0.29
Nodes (5): Sf2Export, Illuminate\Contracts\View\View, Maatwebsite\Excel\Concerns\FromView, Maatwebsite\Excel\Concerns\WithEvents, Maatwebsite\Excel\Concerns\WithTitle

### Community 63 - "App Http Controllers"
Cohesion: 0.36
Nodes (3): QrCardController, Illuminate\Http\Response, Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 66 - "Modal Conversion Guide"
Cohesion: 0.27
Nodes (10): Alpine x-data Multi-Modal State Pattern, Shared form-inline.blade.php Partial Pattern, x-icon-btn Component, Modal Conversion Pattern (inline CRUD modals), Modal + Icon Conversion Guide, Modal Markup Pattern (fade + scale, backdrop, ESC close), Alpine.js, Icon-Based Actions (view/edit/delete) (+2 more)

### Community 70 - "App Imports Studentsimport"
Cohesion: 0.36
Nodes (5): StudentsImport, Maatwebsite\Excel\Concerns\SkipsEmptyRows, Maatwebsite\Excel\Concerns\ToModel, Maatwebsite\Excel\Concerns\WithHeadingRow, Maatwebsite\Excel\Concerns\WithValidation

### Community 71 - "Composer Require Dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision (+1 more)

### Community 77 - "Ref Php Artisan"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 83 - "App Models Attendancesetting"
Cohesion: 0.29
Nodes (3): AttendanceSetting, self, AcademicStructureSeeder

### Community 84 - "Composer Allow Plugins"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 85 - "Composer Require Laravel"
Cohesion: 0.29
Nodes (7): require, barryvdh/laravel-dompdf, endroid/qr-code, laravel/framework, laravel/tinker, maatwebsite/excel, php

### Community 86 - "Package"
Cohesion: 0.29
Nodes (6): private, $schema, scripts, build, dev, type

### Community 90 - "Public Deped Logo"
Cohesion: 0.53
Nodes (6): DepEd Logo (Department of Education Philippines), Blue/Red/Gold Institutional Palette, Department of Education (Philippines), Official Report Branding Asset (SF1/SF2 Forms), Torch Emblem with Red Flame, DepED Wordmark and 'Department of Education' Text

### Community 91 - "Public Logo Blue"
Cohesion: 0.47
Nodes (6): Blue and White Scalloped-Seal Brand Identity, Dahile Provincial Community High School, Emblem Imagery: Open Book, Torch of Knowledge, Skilled Worker, Graduation Cap and Diploma, Founding Year MCMXCVI (1996), Motto: Qualitas Educationis (Quality of Education), Dahile Provincial Community High School Seal

### Community 94 - "Composer Psr 4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 95 - "Public Eaj Primary"
Cohesion: 0.60
Nodes (5): EAJ Web Development Services Brand, Navy, White, and Pink-Red Brand Palette, EA Monogram Mark, EAJ Web Development Services Primary Logo, School Attendance Application

### Community 96 - "Public School Logos"
Cohesion: 0.50
Nodes (5): Dahile Provincial Community High School Seal (uploaded logo), Motto: Qualitas Educationis, SF1/SF2 report header branding asset, School Identity: Dahile Provincial Community High School, est. MCMXCVI (1996), School logo upload storage (public/school-logos, hashed filename)

### Community 99 - "Ref Php Artisan"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 103 - "Profile Partials Update"
Cohesion: 0.50
Nodes (3): profile.partials.delete-user-form, profile.partials.update-password-form, profile.partials.update-profile-information-form

### Community 104 - "Public Eaj Appicon"
Cohesion: 0.67
Nodes (4): Icon Design: Navy Squircle with Pink/White EA Monogram, EAJ Systems Brand Identity, EAJ App Icon (EA Monogram), School Attendance Application (DepEd SF2)

### Community 105 - "Public School Logos"
Cohesion: 0.67
Nodes (4): Dahile Provincial Community High School Seal (uploaded logo), SF1/SF2 Report Header Branding, Dahile Provincial Community High School (est. MCMXCVI / 1996), School Logo Upload Storage (hashed-filename public asset)

### Community 106 - "Composer Extra"
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
- **108 isolated node(s):** `admin.items.form`, `$schema`, `name`, `type`, `description` (+103 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **65 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **What is the exact relationship between `Blue/Red/Gold Institutional Palette` and `Official Report Branding Asset (SF1/SF2 Forms)`?**
  _Edge tagged AMBIGUOUS (relation: conceptually_related_to) - confidence is low._
- **What is the exact relationship between `Dahile Provincial Community High School (est. MCMXCVI / 1996)` and `SF1/SF2 Report Header Branding`?**
  _Edge tagged AMBIGUOUS (relation: shares_data_with) - confidence is low._
- **What is the exact relationship between `School Identity: Dahile Provincial Community High School, est. MCMXCVI (1996)` and `SF1/SF2 report header branding asset`?**
  _Edge tagged AMBIGUOUS (relation: shares_data_with) - confidence is low._
- **Why does `User` connect `User Roles & Policies` to `Subscription Plans & Pricing`, `Learner Promotion Flow`, `Admin Audit & Billing`, `Payments & PayMongo Billing`, `Subject Management`, `QR Scan Portal & Schedules`, `Attendance Records`, `Student Model & School Year`, `Admin Section & Search`, `Teacher Registration Approval`, `User Registration & Profile`, `Class Session Board`, `Attendance Logs & Unlocks`, `Attendance Service Rules`, `Admin Dashboard & Payments`, `App Services Paymongoservice`, `Database Seeders Adminuserseeder`, `App Models Schoolyear`, `App Http Controllers`, `Tests Feature Schoolscopingtest`, `Tests Feature Sf3Reporttest`, `App Models User`, `App Policies Sectionpolicy`, `App Models Teacher`, `Tests Feature Schoolidverificationtest`, `Tests Feature Teacherscheduletest`, `Tests Feature Subscriptionsettlementtest`, `App Models Gradelevel`, `Tests Feature Reportschooltest`, `Tests Feature Sf1Reporttest`, `Tests Feature Sf5Reporttest`, `Tests Feature Subscriptionproductionread`, `Tests Feature Notificationbelltest`, `App Http Controllers`, `App Services Insightsservice`, `App Models Holiday`, `App Http Controllers`, `Tests Feature Insightstest`, `Tests Unit Attendanceservicetest`, `App Http Controllers`, `Tests Feature Enrollmenttest`, `Tests Feature Sf2Reporttest`, `Tests Feature Subscriptiontest`, `App Services Notificationservice`, `Tests Feature Settingcachingtest`, `Tests Feature Tenantisolationtest`, `Tests Feature Trialcountdowntest`, `Tests Feature Subscribepagestatestest`, `App Http Controllers`, `App Providers Appserviceprovider`, `Tests Feature Compedaccountstatustest`, `Database Factories Sectionfactory`?**
  _High betweenness centrality (0.175) - this node is a cross-community bridge._
- **Why does `School` connect `Tests Feature Schoolscopingtest` to `Learner Promotion Flow`, `Payments & PayMongo Billing`, `QR Scan Portal & Schedules`, `Attendance Records`, `Test Factories & Seeding`, `Admin Section & Search`, `User Registration & Profile`, `Class Session Board`, `Attendance Logs & Unlocks`, `Admin Dashboard & Payments`, `App Services Paymongoservice`, `App Models Schoolyear`, `App Http Controllers`, `Tests Feature Schoolidverificationtest`, `Tests Feature Reportschooltest`, `App Http Controllers`, `Database Seeders Databaseseeder`, `App Http Controllers`, `App Models Setting`, `Tests Feature Subscriptiontest`, `Tests Feature Tenantisolationtest`, `Database Factories Sectionfactory`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **Why does `SchoolYear` connect `Learner Promotion Flow` to `Payments & PayMongo Billing`, `User Roles & Policies`, `QR Scan Portal & Schedules`, `Attendance Records`, `Student Model & School Year`, `Admin Section & Search`, `Teacher Registration Approval`, `Class Session Board`, `Attendance Logs & Unlocks`, `Attendance Service Rules`, `Admin Dashboard & Payments`, `App Models Schoolyear`, `Tests Feature Sf3Reporttest`, `App Http Controllers`, `App Http Controllers`, `App Policies Sectionpolicy`, `Tests Feature Teacherscheduletest`, `App Http Controllers`, `Tests Feature Sf5Reporttest`, `App Models Holiday`, `Tests Feature Insightstest`, `Tests Unit Attendanceservicetest`, `App Http Controllers`, `Tests Feature Enrollmenttest`, `Tests Feature Sf2Reporttest`, `App Models Attendancesetting`, `App Providers Appserviceprovider`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Are the 45 inferred relationships involving `User` (e.g. with `.store()` and `.update()`) actually correct?**
  _`User` has 45 INFERRED edges - model-reasoned connections that need verification._