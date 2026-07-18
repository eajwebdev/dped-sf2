# EAJ ASFS — Automated School Forms System

**Automate DepEd School Forms. Simplify Classroom Reporting.**

EAJ ASFS is a scalable DepEd School Forms automation platform for Philippine schools,
built with Laravel. Teachers record attendance through QR check-in and the system fills
out official DepEd forms automatically — no paperwork, no late nights.

## Currently available

- ✅ **School Form 2 (SF2)** — Daily Attendance Monitoring & Automated Reporting
  - QR check-in: start a class, hand off the scanner, students tap in
  - Learners pre-marked absent; scans flip them to present
  - Manual marking grid (Male / Female tables, A–Z) with autosave and keyboard shortcuts
  - Print-ready SF2 PDF in the official DepEd layout, plus Excel export
  - Printable student QR ID cards, per learner or per section

## Coming soon

- 🔜 Additional DepEd School Forms automation
- 🔜 More teacher and school management tools
- 🔜 Advanced reporting and analytics

## Platform features

- Multi-school (SaaS): each school has its own logo, active school year, and scoped data
- Roles: administrators approve teacher registrations and manage schools, years, and sections
- Weekly teacher schedules, class sessions, and a dedicated scan portal
- Subscription billing with a 2-week free trial on approval

## Tech stack

- Laravel · PHP · MySQL
- Blade + Tailwind CSS + Alpine.js (Vite)
- DomPDF for form rendering · Endroid QR Code

## Development

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

Run tests with `php artisan test`.

---

© EAJ Systems. All rights reserved.
