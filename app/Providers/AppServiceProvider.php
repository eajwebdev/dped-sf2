<?php

namespace App\Providers;

use App\Models\SchoolYear;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Administrators bypass every policy check; teachers fall through to
        // the individual policy rules defined per model.
        Gate::before(fn ($user) => $user->isAdmin() ? true : null);

        // Share the active school year with every view for the top nav / banners.
        View::composer('*', function ($view) {
            $view->with('activeSchoolYear', SchoolYear::current());
        });

        // Audit authentication events.
        Event::listen(Login::class, function (Login $event) {
            app(AuditLogger::class)->log('login', $event->user, 'User logged in');
        });
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                app(AuditLogger::class)->log('logout', $event->user, 'User logged out');
            }
        });
    }
}
