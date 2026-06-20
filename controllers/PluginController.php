<?php
namespace Niyanta\Controllers;

use Niyanta\Core\Flash;
use Niyanta\Core\Log;
use Niyanta\Core\PluginManager;
use Niyanta\Core\View;

class PluginController
{
    public function index(): void
    {
        View::render('settings.plugins', [
            'plugins' => PluginManager::available(),
        ], 'app');
    }

    public function upload(): void
    {
        try {
            $slug = PluginManager::uploadZip($_FILES['plugin'] ?? []);
            Log::record('plugin.upload', $slug);
            Flash::success("Plugin '{$slug}' uploaded. You can now install it.");
        } catch (\Throwable $e) {
            Flash::error('Upload failed: ' . $e->getMessage());
        }
        redirect('/settings/plugins');
    }

    public function install(): void
    {
        $this->run('install', 'installed');
    }

    public function activate(): void
    {
        $this->run('activate', 'activated');
    }

    public function deactivate(): void
    {
        $this->run('deactivate', 'deactivated');
    }

    public function remove(): void
    {
        $this->run('remove', 'removed');
    }

    private function run(string $method, string $pastTense): void
    {
        $slug = (string) request('slug', '');
        try {
            PluginManager::{$method}($slug);
            Log::record('plugin.' . $method, $slug);
            Flash::success("Plugin '{$slug}' {$pastTense}.");
        } catch (\Throwable $e) {
            Flash::error('Action failed: ' . $e->getMessage());
        }
        redirect('/settings/plugins');
    }
}
