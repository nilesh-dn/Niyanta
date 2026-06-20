<?php
/**
 * Application bootstrap: autoloading, helpers and core initialisation.
 * Returns true once the app is ready to dispatch, or false if not yet installed.
 */

require_once __DIR__ . '/helpers.php';

// Simple PSR-4 style autoloader for the Niyanta namespace.
spl_autoload_register(static function (string $class): void {
    $prefix = 'Niyanta\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    // Niyanta\Core\X      -> core/X.php
    // Niyanta\Controllers\X -> controllers/X.php
    $relative = str_replace('\\', '/', $relative);
    $map = [
        'Core/'        => 'core/',
        'Controllers/' => 'controllers/',
    ];
    foreach ($map as $nsDir => $pathDir) {
        if (str_starts_with($relative, $nsDir)) {
            $file = base_path($pathDir . substr($relative, strlen($nsDir)) . '.php');
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});

use Niyanta\Core\Config;
use Niyanta\Core\Auth;
use Niyanta\Core\PluginManager;

Config::load();

if (!Config::isInstalled()) {
    return false;
}

Auth::startSession();

// Load active plugins so they can register routes/menus/permissions/widgets
// before the core routes are dispatched.
PluginManager::loadActive();

return true;
