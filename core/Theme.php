<?php
namespace Niyanta\Core;

/**
 * Dark/light theme preference resolution. The preference is stored per user
 * (users.theme_pref) and mirrored into localStorage on the client so the
 * correct theme can be applied before login without a flash.
 */
class Theme
{
    public const LIGHT = 'light';
    public const DARK  = 'dark';

    public static function current(): string
    {
        $user = Auth::user();
        $pref = $user['theme_pref'] ?? ($_COOKIE['niyanta_theme'] ?? self::LIGHT);
        return $pref === self::DARK ? self::DARK : self::LIGHT;
    }

    /** Persist a preference for the authenticated user (and a cookie hint). */
    public static function set(string $theme): void
    {
        $theme = $theme === self::DARK ? self::DARK : self::LIGHT;
        setcookie('niyanta_theme', $theme, time() + 60 * 60 * 24 * 365, '/');
        if (Auth::check()) {
            Database::query('UPDATE users SET theme_pref = ? WHERE id = ?', [$theme, Auth::id()]);
        }
    }
}
