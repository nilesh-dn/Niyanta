<?php
/**
 * Niyanta front controller — single entry point for every request.
 */

declare(strict_types=1);

error_reporting(E_ALL);

$ready = require __DIR__ . '/core/bootstrap.php';

// Not installed yet: hand off to the installation wizard.
if ($ready === false) {
    require __DIR__ . '/install/index.php';
    exit;
}

use Niyanta\Core\Config;
use Niyanta\Core\Router;

ini_set('display_errors', Config::get('debug', false) ? '1' : '0');

// Register core routes, menus and dashboard widgets (plugins already loaded).
require __DIR__ . '/core/routes.php';
require __DIR__ . '/core/widgets.php';

Router::dispatch();
