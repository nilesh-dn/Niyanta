<?php
namespace Niyanta\Core;

/**
 * Company branding, settings store and colour palettes.
 */
class Branding
{
    private static ?array $settings = null;

    /** Load the key/value settings table once per request. */
    private static function settings(): array
    {
        if (self::$settings === null) {
            self::$settings = [];
            foreach (Database::all('SELECT `key`, `value` FROM settings') as $row) {
                self::$settings[$row['key']] = $row['value'];
            }
        }
        return self::$settings;
    }

    public static function setting(string $key, $default = null)
    {
        $settings = self::settings();
        if (!array_key_exists($key, $settings)) {
            return $default;
        }
        $value = $settings[$key];
        $decoded = json_decode((string) $value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public static function set(string $key, $value): void
    {
        $stored = is_scalar($value) ? (string) $value : json_encode($value);
        Database::query(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $stored]
        );
        self::$settings = null;
    }

    public static function companyName(): string
    {
        return (string) self::setting('company_name', 'ABC Pvt Ltd.');
    }

    public static function logoUrl(): ?string
    {
        $logo = self::setting('logo');
        return $logo ? asset('uploads/logos/' . $logo) : null;
    }

    public static function palettes(): array
    {
        return Database::all('SELECT * FROM palettes ORDER BY id ASC');
    }

    public static function activePalette(): array
    {
        $palette = Database::first('SELECT * FROM palettes WHERE is_active = 1 LIMIT 1');
        return $palette ?: [
            'name'      => 'Default',
            'primary'   => '#0B1F4D',
            'secondary' => '#6EC1FF',
            'accent'    => '#FFD84D',
        ];
    }

    public static function setActivePalette(int $id): void
    {
        Database::query('UPDATE palettes SET is_active = 0');
        Database::query('UPDATE palettes SET is_active = 1 WHERE id = ?', [$id]);
    }

    public static function createPalette(string $name, string $primary, string $secondary, string $accent): int
    {
        return Database::insert(
            'INSERT INTO palettes (name, `primary`, secondary, accent, is_active) VALUES (?, ?, ?, ?, 0)',
            [$name, $primary, $secondary, $accent]
        );
    }

    public static function deletePalette(int $id): void
    {
        Database::query('DELETE FROM palettes WHERE id = ? AND is_active = 0', [$id]);
    }

    /** CSS custom properties for the active palette, injected into <head>. */
    public static function cssVariables(): string
    {
        $p = self::activePalette();
        return ':root{'
            . '--color-primary:' . self::safeColor($p['primary'] ?? '#0B1F4D') . ';'
            . '--color-secondary:' . self::safeColor($p['secondary'] ?? '#6EC1FF') . ';'
            . '--color-accent:' . self::safeColor($p['accent'] ?? '#FFD84D') . ';'
            . '}';
    }

    private static function safeColor(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) ? $color : '#0B1F4D';
    }
}
