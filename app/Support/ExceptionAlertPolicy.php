<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Decides whether an exception is worth emailing the admin about. Kept as a
 * pure function of (exception, environment) so it's testable without
 * depending on the real app() environment, which is fixed at boot and can't
 * be changed at runtime (e.g. from within a test).
 */
class ExceptionAlertPolicy
{
    public static function shouldAlert(Throwable $e, string $environment): bool
    {
        if ($environment !== 'production') {
            return false;
        }

        // 4xx HTTP exceptions (404s from bot traffic, 403s, 419 CSRF expiry)
        // and auth-related redirects are normal, expected outcomes, not bugs.
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
            return false;
        }

        if ($e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof TokenMismatchException
            || $e instanceof NotFoundHttpException) {
            return false;
        }

        return true;
    }
}
