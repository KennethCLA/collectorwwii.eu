<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StartSessionForExistingCookie
{
    /**
     * Public catalogue pages do not need a server-side session for anonymous
     * GET requests. Existing visitors keep normal auth/session behaviour;
     * first-time anonymous visitors do not create a Redis session record.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->hasStoredSession($request)) {
            $request->attributes->set('sessionless_catalogue', true);

            return $next($request);
        }

        return app(StartSession::class)->handle($request, $next);
    }

    /**
     * Do not treat the mere presence of a cookie as a session. Laravel will
     * otherwise accept a client-invented session ID and persist it on the
     * response, allowing crawlers to create unlimited Redis records.
     *
     * EncryptCookies has already decrypted the cookie in the web middleware
     * stack. Reading a session handler is read-only for the configured Redis,
     * database, file, and array handlers.
     */
    private function hasStoredSession(Request $request): bool
    {
        $cookieName = config('session.cookie');
        $sessionId = is_string($cookieName) ? $request->cookie($cookieName) : null;

        // Laravel's server-side session IDs are 40-character alpha-numeric
        // strings. Reject malformed client input before touching the handler.
        if (! is_string($sessionId) || ! preg_match('/^[A-Za-z0-9]{40}$/', $sessionId)) {
            return false;
        }

        try {
            $payload = app('session')->driver()->getHandler()->read($sessionId);
        } catch (Throwable) {
            // A transient session-store failure must not turn anonymous
            // catalogue browsing into a session-creating operation.
            return false;
        }

        return is_string($payload) && $payload !== '';
    }
}
