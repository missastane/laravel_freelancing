<?php

namespace App\Support;

class CsrfToken
{
    /**
     * Generate a signed CSRF token.
     */
    public function generate(): string
    {
        $nonce = bin2hex(random_bytes(32));

        $signature = hash_hmac(
            'sha256',
            $nonce,
            config('app.key')
        );

        return $nonce . '.' . $signature;
    }

    /**
     * Validate a signed CSRF token.
     */
    public function isValid(?string $token): bool
    {
        if (!$token || !str_contains($token, '.')) {
            return false;
        }

        [$nonce, $signature] = explode('.', $token, 2);

        if (
            strlen($nonce) !== 64 ||
            strlen($signature) !== 64 ||
            !ctype_xdigit($nonce) ||
            !ctype_xdigit($signature)
        ) {
            return false;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $nonce,
            config('app.key')
        );

        return hash_equals($expectedSignature, $signature);
    }
}
