<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Twofactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->two_factor_auth)
        {
            return $next($request);
        } else {
            return response()->json([
                'message' => 'Two-factor authentication required',
                'data' => null,
                'error' => true
            ], 400);
        }
    }
}