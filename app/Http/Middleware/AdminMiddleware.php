<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden — Admins only'], 403);
        }

        if (!Auth::user()->isActive()) {
            return response()->json(['message' => 'Your account is deactivated'], 403);
        }

        return $next($request);
    }
}