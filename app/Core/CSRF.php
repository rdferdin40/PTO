<?php
declare(strict_types=1);

namespace App\Core;

/**
 * CSRF Protection
 *
 * Generates and validates CSRF tokens
 */
class CSRF
{
    /**
     * Generate CSRF token
     */
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }

        return Session::get('_csrf_token');
    }

    /**
     * Validate CSRF token
     */
    public static function validate(string $token): bool
    {
        return hash_equals(self::token(), $token);
    }

    /**
     * Generate hidden input field
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::token()) . '">';
    }
}
