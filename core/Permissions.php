<?php
namespace Niyanta\Core;

/**
 * Capability-based permission system.
 *
 * Permissions are string slugs. Roles aggregate permissions through the
 * role_permissions table; individual users may receive extra grants via
 * user_permissions. The super_admin role implicitly holds every permission
 * (the '*' wildcard), so new plugin permissions work without configuration.
 *
 * Plugins register new permission slugs with Permissions::register(); they are
 * stored in the permissions table and can then be attached to any role —
 * including future custom roles — with no core changes.
 */
class Permissions
{
    private static ?array $cache = null;

    /** Effective permission slugs for the current user. */
    public static function forCurrentUser(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $user = Auth::user();
        if (!$user) {
            return self::$cache = [];
        }

        // Super admin shortcut.
        if (($user['role_slug'] ?? '') === 'super_admin') {
            return self::$cache = ['*'];
        }

        $rolePerms = Database::all(
            'SELECT p.slug FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = ?',
            [$user['role_id']]
        );
        $userPerms = Database::all(
            'SELECT p.slug FROM user_permissions up
             JOIN permissions p ON p.id = up.permission_id
             WHERE up.user_id = ?',
            [$user['id']]
        );

        $slugs = array_merge(
            array_column($rolePerms, 'slug'),
            array_column($userPerms, 'slug')
        );
        return self::$cache = array_values(array_unique($slugs));
    }

    public static function can(string $permission): bool
    {
        $perms = self::forCurrentUser();
        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    /** Abort with 403 if the current user lacks the permission. */
    public static function require(string $permission): void
    {
        if (!self::can($permission)) {
            http_response_code(403);
            View::render('errors/403', ['permission' => $permission], 'app');
            exit;
        }
    }

    /**
     * Register a permission slug (idempotent). Used by plugins during activation.
     * Optionally attach it to one or more role slugs.
     *
     * @param string[] $assignTo Role slugs to grant this permission to.
     */
    public static function register(string $slug, string $description = '', array $assignTo = []): int
    {
        $existing = Database::scalar('SELECT id FROM permissions WHERE slug = ?', [$slug]);
        if ($existing) {
            $id = (int) $existing;
        } else {
            $id = Database::insert(
                'INSERT INTO permissions (slug, description) VALUES (?, ?)',
                [$slug, $description]
            );
        }
        foreach ($assignTo as $roleSlug) {
            $roleId = Database::scalar('SELECT id FROM roles WHERE slug = ?', [$roleSlug]);
            if ($roleId) {
                Database::query(
                    'INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                    [(int) $roleId, $id]
                );
            }
        }
        return $id;
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
