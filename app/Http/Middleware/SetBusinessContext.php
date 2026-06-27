<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Business\Business;

class SetBusinessContext
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->business_id) {
                $cacheKey = 'business_context_' . $user->business_id;
                
                $business = Cache::remember($cacheKey, 3600, function () use ($user) {
                    return Business::find($user->business_id);
                });

                if ($business && $business->is_active) {
                    session(['current_business_id' => $business->id]);
                    session(['current_business' => $business]);
                }
            }
        }

        return $next($request);
    }
}
