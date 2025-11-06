<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableCsrfForWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('webhook/tumipay')) {
            // Skip CSRF verification for webhook by bypassing the VerifyCsrfToken middleware
            return app()->call($next, [$request]);
        }

        return $next($request);
    }
}
