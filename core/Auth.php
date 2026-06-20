<?php
namespace Niyanta\Core;

/**
 * Session-based authentication.
 */
class Auth
{
    private static ?array $user = null;
    private static bool $resolved = false;

    /** Start a hardened session stored under /storage/sessions. */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $sessionPath = base_path('storage/sessions');
        if (is_dir($sessionPath) && is_writable($sessionPath)) {
            session_save_path($sessionPath);
        }
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);
        session_name('niyanta_session');
        session_start();
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::first(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? AND u.status = "active" LIMIT 1',
            [$email]
        );
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Prevent session fixation.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        self::$user = $user;
        self::$resolved = true;
        Permissions::flush();
        Hooks::doAction('after_login', $user);
        return true;
    }

    public static function logout(): void
    {
        Hooks::doAction('before_logout', self::$user);
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        self::$user = null;
        self::$resolved = true;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /** The authenticated user row (with role_slug/role_name), or null. */
    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$user;
        }
        self::$resolved = true;
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) {
            return self::$user = null;
        }
        self::$user = Database::first(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? LIMIT 1',
            [(int) $id]
        );
        return self::$user;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    /** Redirect guests to the login page. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::error('Please sign in to continue.');
            redirect('/login');
        }
    }
}
