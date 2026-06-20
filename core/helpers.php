<?php
/**
 * Global helper functions for Niyanta.
 * Loaded first during bootstrap; intentionally framework-free.
 */

if (!function_exists('e')) {
    /** Escape a string for safe HTML output. */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_path')) {
    /** Absolute filesystem path to the project root, optionally appended. */
    function base_path(string $append = ''): string
    {
        $root = dirname(__DIR__);
        return $append === '' ? $root : $root . '/' . ltrim($append, '/');
    }
}

if (!function_exists('url')) {
    /** Build an application URL honouring the configured base path. */
    function url(string $path = ''): string
    {
        $base = \Niyanta\Core\Config::get('base_path', '');
        $base = rtrim((string) $base, '/');
        $path = '/' . ltrim($path, '/');
        return $base . ($path === '/' ? '/' : rtrim($path, '/'));
    }
}

if (!function_exists('asset')) {
    /** URL to a static asset under /assets, /themes or /uploads. */
    function asset(string $path): string
    {
        return url($path);
    }
}

if (!function_exists('redirect')) {
    /** Send a redirect response and stop execution. */
    function redirect(string $path): void
    {
        $location = preg_match('#^https?://#', $path) ? $path : url($path);
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('old')) {
    /** Retrieve a previously submitted form value flashed into the session. */
    function old(string $key, $default = '')
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('request')) {
    /** Read a value from POST then GET. */
    function request(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}

if (!function_exists('csrf_field')) {
    /** Hidden input carrying the current CSRF token. */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(\Niyanta\Core\Csrf::token()) . '">';
    }
}

if (!function_exists('can')) {
    /** Convenience wrapper around the permission system. */
    function can(string $permission): bool
    {
        return \Niyanta\Core\Permissions::can($permission);
    }
}

if (!function_exists('auth')) {
    /** Currently authenticated user array, or null. */
    function auth(): ?array
    {
        return \Niyanta\Core\Auth::user();
    }
}

if (!function_exists('setting')) {
    /** Read an application setting with a default fallback. */
    function setting(string $key, $default = null)
    {
        return \Niyanta\Core\Branding::setting($key, $default);
    }
}
