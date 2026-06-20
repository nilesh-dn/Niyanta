<?php
/**
 * Core route registration. Plugins register their own routes during
 * PluginManager::loadActive(); these are the built-in platform routes.
 */

use Niyanta\Core\Router;
use Niyanta\Core\Menu;
use Niyanta\Controllers\AuthController;
use Niyanta\Controllers\DashboardController;
use Niyanta\Controllers\ProfileController;
use Niyanta\Controllers\BrandingController;
use Niyanta\Controllers\PluginController;
use Niyanta\Controllers\SettingsController;

// --- Authentication (public) ---
Router::get('/', [DashboardController::class, 'index']);
Router::get('/login', [AuthController::class, 'showLogin'], ['auth' => false]);
Router::post('/login', [AuthController::class, 'login'], ['auth' => false]);
Router::post('/logout', [AuthController::class, 'logout']);

// --- Dashboard ---
Router::get('/dashboard', [DashboardController::class, 'index']);

// --- Profile & theme (any authenticated user) ---
Router::get('/profile', [ProfileController::class, 'show']);
Router::post('/profile', [ProfileController::class, 'update']);
Router::post('/theme', [ProfileController::class, 'theme']);

// --- Branding & palettes (super admin) ---
Router::get('/settings/branding', [BrandingController::class, 'index'], ['permission' => 'manage_branding']);
Router::post('/settings/branding', [BrandingController::class, 'update'], ['permission' => 'manage_branding']);
Router::post('/settings/palettes', [BrandingController::class, 'createPalette'], ['permission' => 'manage_palettes']);
Router::post('/settings/palettes/activate', [BrandingController::class, 'activatePalette'], ['permission' => 'manage_palettes']);
Router::post('/settings/palettes/delete', [BrandingController::class, 'deletePalette'], ['permission' => 'manage_palettes']);

// --- Plugins (super admin) ---
Router::get('/settings/plugins', [PluginController::class, 'index'], ['permission' => 'manage_plugins']);
Router::post('/settings/plugins/upload', [PluginController::class, 'upload'], ['permission' => 'manage_plugins']);
Router::post('/settings/plugins/install', [PluginController::class, 'install'], ['permission' => 'manage_plugins']);
Router::post('/settings/plugins/activate', [PluginController::class, 'activate'], ['permission' => 'manage_plugins']);
Router::post('/settings/plugins/deactivate', [PluginController::class, 'deactivate'], ['permission' => 'manage_plugins']);
Router::post('/settings/plugins/remove', [PluginController::class, 'remove'], ['permission' => 'manage_plugins']);

// --- General settings & logs ---
// The /settings landing is auth-only; the controller routes the user to the
// first settings section they can access (keeps the hub correct for custom roles).
Router::get('/settings', [SettingsController::class, 'index']);
Router::post('/settings', [SettingsController::class, 'update'], ['permission' => 'manage_settings']);
Router::get('/settings/logs', [SettingsController::class, 'logs'], ['permission' => 'view_logs']);

// --- Core sidebar menu ---
Menu::add(['label' => 'Dashboard', 'route' => '/dashboard', 'icon' => 'bi-speedometer2', 'order' => 0]);
Menu::add(['label' => 'My Profile', 'route' => '/profile', 'icon' => 'bi-person', 'order' => 90]);
// A single Settings entry opens the tabbed hub (Branding/Plugins/Logs live inside).
Menu::add([
    'label' => 'Settings', 'route' => '/settings', 'icon' => 'bi-gear', 'order' => 200,
    'permission_any' => ['manage_settings', 'manage_branding', 'manage_palettes', 'manage_plugins', 'view_logs'],
]);
