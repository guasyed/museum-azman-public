<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust proxy protocol/port/for, but not forwarded host, to avoid
        // generating asset URLs from internal proxy hostnames/IPs.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PORT
        );

        // Enforce a consistent CSP from app-side for all web responses.
        $middleware->append(\App\Http\Middleware\SetContentSecurityPolicy::class);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please refresh and try again.',
                ], 419);
            }

            if ($request->user()) {
                return redirect()
                    ->back(fallback: route('dashboard'))
                    ->withErrors(['session' => 'Session expired. Please try your action again.']);
            }

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Session expired. Please sign in again.']);
        });
    })->create();
