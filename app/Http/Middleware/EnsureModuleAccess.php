<?php

namespace App\Http\Middleware;

use App\Support\SubscriptionPlans;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Plan-tier gate for School Form modules: `module:sf3` lets Professional and
 * Enterprise subscribers through and sends a Starter subscriber to the
 * subscribe page with an upgrade prompt instead of a bare 403.
 */
class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if ($user && $user->hasModule($module)) {
            return $next($request);
        }

        $required = SubscriptionPlans::MODULE_MIN_PLAN[strtolower($module)] ?? SubscriptionPlans::PROFESSIONAL;
        $label = ['insights' => 'The Advanced Reports dashboard'][strtolower($module)] ?? strtoupper($module);

        return redirect()->route('subscribe.show')->with('error', sprintf(
            '%s is part of the %s plan. Upgrade to unlock it — you only pay the difference for the months you have left.',
            $label,
            SubscriptionPlans::find($required)['name'],
        ));
    }
}
