<?php
namespace Niyanta\Core;

/**
 * Per-session CSRF token issuing and verification.
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }

    /** Abort the request if the submitted CSRF token is invalid. */
    public static function check(): void
    {
        if (!self::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Invalid or expired form token. Please go back and try again.';
            exit;
        }
    }
}
