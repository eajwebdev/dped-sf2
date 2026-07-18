# Deployment checklist

Work through this before the first production deploy. The items under
"Blockers" are the ones that cause data leaks, lost payments, or locked-out
users if skipped.

## Blockers

### 1. Environment

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-real-domain
```

`APP_DEBUG=true` renders full stack traces to end users on any unhandled
error, including database credentials and other env values. This is the single
most common way a Laravel app leaks its secrets.

Generate a key if the server does not already have one:

```bash
php artisan key:generate
```

### 2. PayMongo webhook secret

The webhook at `POST /subscription/webhook` is unauthenticated and CSRF-exempt
by necessity — PayMongo's servers call it directly. The HMAC signature is the
only thing distinguishing a real payment callback from a stranger granting
themselves a paid subscription.

`PayMongoService::verifyWebhookSignature()` **fails closed** outside
`local`/`testing`: with no secret configured, every callback is rejected with
401 and no subscription will ever activate. So this is both a security
requirement and a functional one.

1. In the PayMongo dashboard, create a webhook pointing at
   `https://<your-domain>/subscription/webhook`.
2. Subscribe it to `checkout_session.payment.paid` and `payment.paid`.
3. Copy the signing secret into `PAYMONGO_WEBHOOK_SECRET` (or set it from the
   admin settings screen, which takes precedence over env).

Verify with a sandbox payment before going live — a real one should flip the
`subscription_payments` row to `paid` and extend the user's access.

### 3. Mail

`MAIL_MAILER=log` writes password-reset emails to a log file instead of
sending them. Teachers who forget their password will have no way back in.
Configure a real SMTP transport and send yourself a reset to confirm.

## Standard release steps

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Re-run the cache commands on every deploy; a stale config cache will silently
serve the previous release's environment.

## School-year data

The per-day school calendar is derived from each school year's start and end
dates. `SchoolYear` regenerates it automatically whenever those dates change,
but a calendar that predates that hook can still be short — and any date with
no calendar row reads as a non-class day, which locks attendance for every
teacher in that year.

After deploying, confirm the active year's calendar covers its full range:

```bash
php artisan tinker
>>> $sy = App\Models\SchoolYear::where('is_active', true)->first();
>>> [$sy->start_date->toDateString(), $sy->end_date->toDateString()];
>>> [App\Models\SchoolCalendar::where('school_year_id',$sy->id)->min('date'),
...  App\Models\SchoolCalendar::where('school_year_id',$sy->id)->max('date')];
```

If the calendar span is narrower than the year's range, regenerate it:

```php
app(App\Services\SchoolCalendarService::class)->generate($sy);
```

Admin-set overrides (`is_override = true`) survive regeneration.

## Post-deploy smoke test

- Log in as an admin and as a teacher.
- Open an attendance sheet for today and save a mark.
- Generate an SF2 for a section with learners — it streams as an inline PDF.
- Generate an SF1 for an advisory section and check the 20-column grid is
  legible with your real learner names and addresses.
- Run one sandbox subscription payment end to end.
