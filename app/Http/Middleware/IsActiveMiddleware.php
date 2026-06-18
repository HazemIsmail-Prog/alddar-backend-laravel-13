<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsActiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user()){
            if (!$request->user()->is_active) {
                // logout
                $request->user()->tokens()->delete();
                return response()->json(['message' => 'Your account is suspended'], 401);
            }
        }
        return $next($request);
    }
}
