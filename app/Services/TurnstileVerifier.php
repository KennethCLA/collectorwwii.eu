<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TurnstileVerifier
{
    /**
     * Verify a Cloudflare Turnstile response. A network error or malformed
     * response is deliberately treated as a failure when Turnstile is enabled.
     */
    public function passes(string $token, ?string $remoteIp): bool
    {
        $secret = config('services.turnstile.secret_key');

        if (! $secret) {
            return true;
        }

        try {
            return Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array_filter([
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]))
                ->json('success') === true;
        } catch (\Throwable) {
            return false;
        }
    }
}
