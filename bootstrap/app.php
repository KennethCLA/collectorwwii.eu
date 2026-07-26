<?php

use App\Mail\ExceptionOccurred;
use App\Support\ExceptionAlertPolicy;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'is_admin'])
                ->prefix('admin')
                ->as('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
        $middleware->remove(\Illuminate\Http\Middleware\FrameGuard::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (\Throwable $e) {
            if (! ExceptionAlertPolicy::shouldAlert($e, app()->environment())) {
                return;
            }

            $notifyAddress = config('collector.contact_notify_address');
            if (! $notifyAddress) {
                return;
            }

            // Throttle: at most one email per unique error location per hour,
            // so a recurring/looping error doesn't flood the inbox.
            $key = 'exception-alert:'.md5(get_class($e).$e->getFile().$e->getLine());
            if (Cache::has($key)) {
                return;
            }
            Cache::put($key, true, now()->addHour());

            try {
                Mail::to($notifyAddress)->send(new ExceptionOccurred($e, request()->fullUrl()));
            } catch (\Throwable $ignore) {
                // Never let a failure to send the alert itself break the
                // original exception's normal handling/logging.
            }
        });
    })->create();
