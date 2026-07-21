# Production Deploy Checklist

Run top to bottom. Commands assume you are in the project root on the production server.

> **Why this matters:** your production `schools` table is missing the `education_level`
> column (confirmed from the DB dump), and the `supervisor` role + `learner_values.behavior`
> column are also dev-only. SF9 levels, the school seeder, and principal accounts will error
> until you migrate. Step 3 is the hard blocker.

---

## 0. Back up the database FIRST

Production has real data. Take a full backup before touching migrations.

```bash
mysqldump -u <user> -p <database> > backup-$(date +%F-%H%M).sql
```

Keep this file. If anything goes wrong in step 3, you restore from here.

---

## 1. Maintenance mode (optional but recommended)

```bash
php artisan down --render="errors::503"
```

## 2. Deploy code & dependencies

```bash
git pull                # or however you deploy
composer install --no-dev --optimize-autoloader
```

## 3. Run migrations  ← the blocker

```bash
php artisan migrate --force
```

This applies everything production is missing, including:

- `add_education_level_to_schools` — the column your dump was missing
- `add_supervisor_to_users_role_enum` — enables the read-only Principal role
- `create_learner_grades` / `create_learner_values` — SF9 grade & values storage
- `add_behavior_to_learner_values` — SF9 core-values behavior statements

**If any migration errors, STOP** and restore from the backup (step 0). Do not continue.

---

## 4. Seed — selectively (NOT `db:seed` / `DatabaseSeeder`)

The full `DatabaseSeeder` chain includes demo/default-password accounts. On production,
run only what you need:

### 4a. Schools (safe, idempotent — recommended)

Adds/updates the 17 schools and tags them all JHS + SHS. Keyed on DepEd School ID,
so it never duplicates or clobbers existing rows.

```bash
php artisan db:seed --class=SchoolSeeder --force
```

### 4b. Active school year (only if intended)

`SecondYearSeeder` creates SY 2026-2027, **makes it the active year, and deactivates
every other active year.** Run ONLY if you want 2026-2027 to be the global active year.

```bash
php artisan db:seed --class=SecondYearSeeder --force
```

### 4c. Principal account (only if you want a seeded one — set a real password first)

The seeder's default password is `password`. **Never seed that on production.** Provide
a real password (and optionally target another school / email):

```bash
PRINCIPAL_PASSWORD='<a-strong-password>' \
PRINCIPAL_EMAIL='principal@yourschool.ph' \
PRINCIPAL_SCHOOL_ID=5 \
php artisan db:seed --class=PrincipalSeeder --force
```

> Preferred alternative: skip this seeder and have principals **self-register** in the app
> (choose "Principal / School Head"), then approve them from Admin → Registrations. No default
> passwords enter production that way.

### 4d. DO NOT run on production

- `JadeTeacherSeeder` — demo teacher with password `password`.
- `AdminUserSeeder` / `DatabaseSeeder` — default-credential admin + the whole demo chain.
- Real teachers should be created via **self-registration + admin approval**.

---

## 5. Rebuild caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

(Or the single `php artisan optimize`.)

## 6. Exit maintenance mode

```bash
php artisan up
```

---

## 7. Smoke test in a browser (do not skip)

- [ ] Landing page loads; logo, module list, and "How it works" look right.
- [ ] Log in as an existing admin.
- [ ] Admin → Schools: the 17 schools show with the correct level tags (JHS+SHS, etc.).
- [ ] A teacher can open **SF9 → Open PDF** — confirm it's **one landscape page per learner**,
      the vertical month headers read correctly, and the header shows the right **ES / JHS / SHS** tag.
- [ ] Principal flow: register as "Principal / School Head" → approve in Admin → Registrations →
      log in → the read-only oversight dashboard and SF9 work.
- [ ] Confirm the **active school year** shown in the app is the one you intend.

---

## Rollback

If something is wrong after deploy:

1. `php artisan down`
2. Restore the database from the step-0 backup.
3. Redeploy the previous code revision.
4. `php artisan up`

Migrations here are additive (new columns/enum values), so a code rollback plus DB restore
returns you to the pre-deploy state cleanly.
