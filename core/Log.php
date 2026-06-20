<?php
namespace Niyanta\Core;

/**
 * Activity logging to the activity_logs table (viewable under System Logs).
 */
class Log
{
    public static function record(string $action, string $context = ''): void
    {
        try {
            Database::query(
                'INSERT INTO activity_logs (user_id, action, context, ip, created_at) VALUES (?, ?, ?, ?, NOW())',
                [Auth::id(), $action, $context, $_SERVER['REMOTE_ADDR'] ?? '']
            );
        } catch (\Throwable $e) {
            // Logging must never break the request.
        }
    }

    public static function recent(int $limit = 200): array
    {
        return Database::all(
            'SELECT l.*, u.name AS user_name FROM activity_logs l
             LEFT JOIN users u ON u.id = l.user_id
             ORDER BY l.id DESC LIMIT ' . (int) $limit
        );
    }
}
