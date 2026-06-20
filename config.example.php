<?php
/**
 * Niyanta configuration (example).
 *
 * This file is generated automatically by the installation wizard.
 * Copy it to config.php and fill in your values if you prefer manual setup.
 * The presence of config.php is how Niyanta knows it has been installed.
 */

return [
    // Database connection (PDO / MySQL / MariaDB)
    'db' => [
        'host'    => 'localhost',
        'name'    => 'niyanta',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    // Random 32+ char key used for CSRF/session hardening. Keep secret.
    'app_key' => 'change-me-to-a-random-string',

    // Base URL path if installed in a subdirectory, e.g. '/niyanta'. Empty for root.
    'base_path' => '',

    // Set to true only while debugging locally.
    'debug' => false,
];
