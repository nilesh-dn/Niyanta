<?php
namespace Niyanta\Core;

/**
 * WordPress-like action/filter hook system. Plugins use this to extend the
 * core without modifying it.
 *
 *   Hooks::addAction('after_login', fn($user) => ...);
 *   Hooks::doAction('after_login', $user);
 *
 *   Hooks::addFilter('menu_items', fn($items) => $items);
 *   $items = Hooks::applyFilters('menu_items', $items);
 */
class Hooks
{
    /** @var array<string, array<int, callable[]>> */
    private static array $actions = [];
    /** @var array<string, array<int, callable[]>> */
    private static array $filters = [];

    public static function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        self::$actions[$hook][$priority][] = $callback;
    }

    public static function doAction(string $hook, ...$args): void
    {
        if (empty(self::$actions[$hook])) {
            return;
        }
        ksort(self::$actions[$hook]);
        foreach (self::$actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $callback(...$args);
            }
        }
    }

    public static function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        self::$filters[$hook][$priority][] = $callback;
    }

    /** Pass $value through every registered filter and return the result. */
    public static function applyFilters(string $hook, $value, ...$args)
    {
        if (empty(self::$filters[$hook])) {
            return $value;
        }
        ksort(self::$filters[$hook]);
        foreach (self::$filters[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = $callback($value, ...$args);
            }
        }
        return $value;
    }
}
