<?php
/**
 * Hello Example — the canonical Niyanta plugin scaffold.
 *
 * A plugin's index.php returns a Closure (or an object with a register()
 * method). The closure runs on every request while the plugin is active, and
 * uses the core registries to extend the application without touching core.
 *
 * Available registries:
 *   - Router::get()/post()      register routes (with optional permission)
 *   - Menu::add()               add a sidebar item
 *   - Permissions::register()   declare a permission slug and attach to roles
 *   - Dashboard::add()          add a dashboard widget
 *   - Hooks::addAction/Filter   hook into core events and filters
 *   - View::partial()           render plugin views (this folder's /views is
 *                               auto-registered as a view path)
 */

use Niyanta\Core\Router;
use Niyanta\Core\Menu;
use Niyanta\Core\Permissions;
use Niyanta\Core\Dashboard;
use Niyanta\Core\Database;
use Niyanta\Core\View;

return function (): void {

    // 1) Declare a permission and grant it to the core roles. Idempotent —
    //    safe to call on every request; only inserts the first time.
    Permissions::register('hello_use', 'Use the Hello Example plugin', ['manager', 'employee']);

    // 2) Register a route, protected by the permission above. Super Admin
    //    always passes via the wildcard.
    Router::get('/hello', function (): void {
        $messages = Database::all('SELECT * FROM hello_messages ORDER BY id DESC');
        echo View::partial('layouts.app', [
            'content' => View::partial('hello', ['messages' => $messages]),
        ]);
    }, ['permission' => 'hello_use']);

    // 3) Add a sidebar menu item (visible only to users with the permission).
    Menu::add([
        'label'      => 'Hello',
        'route'      => '/hello',
        'icon'       => 'bi-emoji-smile',
        'permission' => 'hello_use',
        'order'      => 50,
    ]);

    // 4) Add a dashboard widget.
    Dashboard::add([
        'key'    => 'hello_example',
        'title'  => 'Hello Example',
        'icon'   => 'bi-emoji-smile',
        'order'  => 5,
        'render' => function (): string {
            $count = (int) Database::scalar('SELECT COUNT(*) FROM hello_messages');
            return '<span>' . $count . '</span>'
                . '<div class="text-muted small">demo messages</div>';
        },
    ]);
};
