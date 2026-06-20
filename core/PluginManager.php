<?php
namespace Niyanta\Core;

use ZipArchive;

/**
 * WordPress-like plugin lifecycle manager.
 *
 *   Upload ZIP -> Extract -> Install -> Activate -> Deactivate -> Remove
 *
 * A plugin lives in /plugins/<slug> and contains:
 *   - plugin.json   metadata: { name, version, author, description }
 *   - index.php     returns a Closure register() OR an object with register()
 *   - install.sql   (optional) schema run once on install
 *
 * The register() callback receives no arguments and uses the core registries
 * (Router, Menu, Permissions, Dashboard, Hooks, View) to extend the system.
 */
class PluginManager
{
    public static function dir(): string
    {
        return base_path('plugins');
    }

    /** Scan the filesystem for available plugins and merge DB install state. */
    public static function available(): array
    {
        $plugins = [];
        $base = self::dir();
        foreach (glob($base . '/*', GLOB_ONLYDIR) ?: [] as $path) {
            $slug = basename($path);
            $meta = self::readManifest($slug);
            if ($meta === null) {
                continue;
            }
            $row = Database::first('SELECT * FROM plugins WHERE slug = ?', [$slug]);
            $plugins[$slug] = [
                'slug'        => $slug,
                'name'        => $meta['name'] ?? $slug,
                'version'     => $meta['version'] ?? '1.0',
                'author'      => $meta['author'] ?? 'Unknown',
                'description' => $meta['description'] ?? '',
                'installed'   => $row !== null,
                'active'      => $row !== null && (int) $row['is_active'] === 1,
            ];
        }
        ksort($plugins);
        return $plugins;
    }

    public static function readManifest(string $slug): ?array
    {
        $file = self::dir() . '/' . self::safeSlug($slug) . '/plugin.json';
        if (!is_file($file)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : null;
    }

    /** Include the active plugins and run their register() callback. */
    public static function loadActive(): void
    {
        $rows = Database::all('SELECT slug FROM plugins WHERE is_active = 1');
        foreach ($rows as $row) {
            self::boot($row['slug']);
        }
    }

    private static function boot(string $slug): void
    {
        $entry = self::dir() . '/' . self::safeSlug($slug) . '/index.php';
        if (!is_file($entry)) {
            return;
        }
        // Let the plugin register its own view directory automatically.
        $viewDir = dirname($entry) . '/views';
        if (is_dir($viewDir)) {
            View::addPath($viewDir);
        }
        $register = require $entry;
        if ($register instanceof \Closure) {
            $register();
        } elseif (is_object($register) && method_exists($register, 'register')) {
            $register->register();
        }
    }

    public static function install(string $slug): void
    {
        $meta = self::readManifest($slug);
        if ($meta === null) {
            throw new \RuntimeException('Plugin manifest not found.');
        }
        if (Database::first('SELECT id FROM plugins WHERE slug = ?', [$slug])) {
            return; // already installed
        }
        $sqlFile = self::dir() . '/' . self::safeSlug($slug) . '/install.sql';
        if (is_file($sqlFile)) {
            self::runSql((string) file_get_contents($sqlFile));
        }
        Database::insert(
            'INSERT INTO plugins (slug, name, version, author, description, is_active, installed_at)
             VALUES (?, ?, ?, ?, ?, 0, NOW())',
            [
                $slug,
                $meta['name'] ?? $slug,
                $meta['version'] ?? '1.0',
                $meta['author'] ?? 'Unknown',
                $meta['description'] ?? '',
            ]
        );
        Hooks::doAction('plugin_installed', $slug);
    }

    public static function activate(string $slug): void
    {
        if (!Database::first('SELECT id FROM plugins WHERE slug = ?', [$slug])) {
            self::install($slug);
        }
        Database::query('UPDATE plugins SET is_active = 1 WHERE slug = ?', [$slug]);
        Permissions::flush();
        Hooks::doAction('plugin_activated', $slug);
    }

    public static function deactivate(string $slug): void
    {
        Database::query('UPDATE plugins SET is_active = 0 WHERE slug = ?', [$slug]);
        Hooks::doAction('plugin_deactivated', $slug);
    }

    /** Remove a plugin: deactivate, delete its DB row and its files. */
    public static function remove(string $slug): void
    {
        Database::query('DELETE FROM plugins WHERE slug = ?', [$slug]);
        self::deleteDir(self::dir() . '/' . self::safeSlug($slug));
        Hooks::doAction('plugin_removed', $slug);
    }

    /**
     * Handle an uploaded plugin ZIP: validate, extract into /plugins with
     * path-traversal protection, then return the detected slug.
     *
     * @param array $file Entry from $_FILES.
     */
    public static function uploadZip(array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed.');
        }
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('The PHP zip extension is required to upload plugins.');
        }
        $tmp = $file['tmp_name'];
        $zip = new ZipArchive();
        if ($zip->open($tmp) !== true) {
            throw new \RuntimeException('Could not open the ZIP archive.');
        }

        // Determine a single top-level folder, reject traversal entries.
        $topLevel = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) {
                continue;
            }
            $name = str_replace('\\', '/', $name);
            if (str_contains($name, '../') || str_starts_with($name, '/')) {
                $zip->close();
                throw new \RuntimeException('Archive contains illegal paths.');
            }
            $segment = explode('/', trim($name, '/'))[0] ?? '';
            if ($segment === '') {
                continue;
            }
            if ($topLevel === null) {
                $topLevel = $segment;
            } elseif ($topLevel !== $segment) {
                $zip->close();
                throw new \RuntimeException('Archive must contain a single plugin folder.');
            }
        }
        if ($topLevel === null) {
            $zip->close();
            throw new \RuntimeException('Archive is empty.');
        }

        $slug = self::safeSlug($topLevel);
        $target = self::dir() . '/' . $slug;
        if (is_dir($target)) {
            $zip->close();
            throw new \RuntimeException('A plugin with this name already exists.');
        }
        $zip->extractTo(self::dir());
        $zip->close();

        if (self::readManifest($slug) === null) {
            self::deleteDir($target);
            throw new \RuntimeException('Uploaded archive is missing a valid plugin.json.');
        }
        Hooks::doAction('plugin_uploaded', $slug);
        return $slug;
    }

    /** Execute a multi-statement SQL script. */
    private static function runSql(string $sql): void
    {
        Database::runScript(Database::pdo(), $sql);
    }

    private static function safeSlug(string $slug): string
    {
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9._-]/', '', $slug) ?? '';
        return trim($slug, '.');
    }

    private static function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
