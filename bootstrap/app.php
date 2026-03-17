<?php

use App\Http\Middleware\SearchThrottleMiddleware;
use App\Http\Middleware\EnsureMinorUserIsApproved;
use App\Http\Middleware\UserRoleMiddleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'role' => UserRoleMiddleware::class,
            'throttle' => SearchThrottleMiddleware::class,
            'role.institution.selected' => \App\Http\Middleware\EnsureRoleAndInstitutionSelectedMiddleware::class,
            'minor.approved' => EnsureMinorUserIsApproved::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The uploaded file is too large.',
                ], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
            }

            $field = $request->routeIs('register', 'users.update-minor-documents')
                ? 'minor_documents'
                : 'file';

            $message = $field === 'minor_documents'
                ? __('auth.minor_documents_too_large')
                : 'The uploaded file is too large.';

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([$field => $message]);
        });
    })->create();
