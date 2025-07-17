<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GlobalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isLoggedIn = session()->has('data');
        $isLoginPage = $request->is('auth/login');
        
        if ($isLoginPage && $isLoggedIn && $request->method() == 'GET') {
            return redirect('/');
        }

        if ($isLoginPage && !$isLoggedIn && $request->method() == 'POST') {
            return $next($request);
        }

        if (!$isLoginPage && !$isLoggedIn) {
            return redirect('/auth/login');
        }

        return $next($request);
    }
}
