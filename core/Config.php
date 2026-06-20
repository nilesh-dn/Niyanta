<?php
namespace Niyanta\Core;

/**
 * Loads and exposes configuration from the generated config.php.
 * Also detects whether the application has been installed yet.
 */
class Config
{
    private static array $data = [];
    private static bool $loaded = false;

    public static function configFile(): string
    {
        return base_path('config.php');
    }

    /** Has the installer generated config.php yet? */
    public static function isInstalled(): bool
    {
        return is_file(self::configFile());
    }

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        if (self::isInstalled()) {
            $data = require self::configFile();
            self::$data = is_array($data) ? $data : [];
        }
        self::$loaded = true;
    }

    /** Dot-notation accessor, e.g. Config::get('db.host'). */
    public static function get(string $key, $default = null)
    {
        self::load();
        $segments = explode('.', $key);
        $value = self::$data;
        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }

    public static function all(): array
    {
        self::load();
        return self::$data;
    }
}
