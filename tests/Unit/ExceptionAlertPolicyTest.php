<?php

namespace Tests\Unit;

use App\Support\ExceptionAlertPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Tests\TestCase;

class ExceptionAlertPolicyTest extends TestCase
{
    public function test_ignores_non_production_environments(): void
    {
        $this->assertFalse(ExceptionAlertPolicy::shouldAlert(new \RuntimeException('boom'), 'local'));
        $this->assertFalse(ExceptionAlertPolicy::shouldAlert(new \RuntimeException('boom'), 'testing'));
        $this->assertFalse(ExceptionAlertPolicy::shouldAlert(new \RuntimeException('boom'), 'staging'));
    }

    public function test_alerts_on_generic_exception_in_production(): void
    {
        $this->assertTrue(ExceptionAlertPolicy::shouldAlert(new \RuntimeException('boom'), 'production'));
    }

    public function test_alerts_on_5xx_http_exception(): void
    {
        $this->assertTrue(ExceptionAlertPolicy::shouldAlert(new ServiceUnavailableHttpException(), 'production'));
    }

    public function test_ignores_404_not_found(): void
    {
        $this->assertFalse(ExceptionAlertPolicy::shouldAlert(new NotFoundHttpException(), 'production'));
    }

    public function test_ignores_validation_exceptions(): void
    {
        $this->assertFalse(ExceptionAlertPolicy::shouldAlert(
            ValidationException::withMessages(['x' => 'required']),
            'production'
        ));
    }

    public function test_ignores_authentication_exceptions(): void
    {
        $this->assertFalse(ExceptionAlertPolicy::shouldAlert(new AuthenticationException(), 'production'));
    }

    public function test_ignores_authorization_exceptions(): void
    {
        $this->assertFalse(ExceptionAlertPolicy::shouldAlert(new AuthorizationException(), 'production'));
    }

    public function test_ignores_token_mismatch_exceptions(): void
    {
        $this->assertFalse(ExceptionAlertPolicy::shouldAlert(new TokenMismatchException(), 'production'));
    }
}
