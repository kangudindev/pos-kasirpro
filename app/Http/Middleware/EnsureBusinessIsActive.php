<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBusinessIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $business = session('current_business');

        if (!$business) {
            abort(403, 'No active business. Please select a business.');
        }

        if (!$business->is_active) {
            abort(403, 'This business is inactive. Please contact administrator.');
        }

        return $next($request);
    }
}
