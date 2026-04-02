<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetContentSecurityPolicy
{
    /**
     * Apply CSP headers to application responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        $policy = implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "img-src 'self' data: https: http:",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "script-src-elem 'self' 'unsafe-inline' blob: https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://*.google-analytics.com",
            "manifest-src 'self'",
            "worker-src 'self' blob:",
        ]);

        $response->headers->set('Content-Security-Policy', $policy);

        return $response;
    }
}