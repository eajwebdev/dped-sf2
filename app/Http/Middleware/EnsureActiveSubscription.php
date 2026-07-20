<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the teacher app behind account approval + an active trial or paid
 * subscription. Admins always pass; pending/rejected accounts are sent to the
 * status page; approved-but-lapsed accounts are sent to the subscribe page.
 */
class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isAdmin()) {
            return $next($request);
        }

        // Supervisors have no business in the teacher app. Every teacher route
        // sits behind this middleware, so bouncing them here confines school
        // heads to their read-only oversight area — they can never reach a
        // teacher write endpoint, regardless of section access.
        if ($user->isSupervisor()) {
            return redirect()->route('supervisor.dashboard');
        }

        if (! $user->isApproved()) {
            return redirect()->route('account.pending');
        }

        if (! $user->hasActiveAccess()) {
            return redirect()->route('subscribe.show')
                ->with('error', 'Your free trial has ended. Subscribe to keep using '.config('app.name').'.');
        }

        return $next($request);
    }
}
