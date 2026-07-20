<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the read-only oversight area. School heads/principals (supervisors) pass,
 * and admins pass too so they can preview it. The account must be approved and
 * active. Nothing here grants write access: the oversight routes are all GETs,
 * and every write policy independently gates on isAdmin().
 */
class EnsureUserIsSupervisor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->isSupervisor() || $user->isAdmin()) && $user->is_active && $user->isApproved(),
            403,
            'Supervisor access required.'
        );

        return $next($request);
    }
}
