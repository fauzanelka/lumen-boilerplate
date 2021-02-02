<?php

namespace App\Http\Middleware;

use Closure;

class ExpectJsonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->expectsJson()) {
            return response()->json(['error' => 'You must accept JSON'], 406);
        }

        return $next($request);
    }
}
