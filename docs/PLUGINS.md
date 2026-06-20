# Niyanta — Developer Guide: Building Modules & Plugins

Niyanta is **modular**. The core platform provides authentication, roles,
branding, theming and a dashboard; every real feature (Employees, Attendance,
Payroll, Leave, Notices, …) ships as a **plugin** that extends the core through
registries and hooks. **No core files are ever modified.**

This guide shows how to build one. The working reference is
[`/plugins/hello-example`](../plugins/hello-example).

---

## 1. Anatomy of a plugin

A plugin is a single folder under `/plugins/<slug>`:

```
plugins/
  employees/
    plugin.json        # required — metadata
    index.php          # required — returns the register() closure
    install.sql        # optional — schema run once on install
    views/             # optional — auto-registered as a view path
      list.php
    controllers/       # optional — your own classes (require them yourself)
    assets/            # optional — css/js you reference from your views
```

### `plugin.json`

```json
{
    "name": "Employees",
    "version": "1.0",
    "author": "Niyanta Team",
    "description": "Employee Management Module"
}
```

### `index.php`

Returns a **Closure** (or an object with a `register()` method). It runs on
every request while the plugin is **active**, *before* core routes are
dispatched, so it can register routes, menus, permissions and widgets.

```php
<?php
use Niyanta\Core\{Router, Menu, Permissions, Dashboard, Hooks, View};

return function (): void {
    Permissions::register('employees_view', 'View employees', ['manager']);

    Router::get('/employees', [\MyPlugin\EmployeeController::class, 'index'], [
        'permission' => 'employees_view',
    ]);

    Menu::add([
        'label' => 'Employees', 'route' => '/employees',
        'icon' => 'bi-people', 'permission' => 'employees_view', 'order' => 10,
    ]);
};
```

> The plugin's `views/` directory is registered automatically, so
> `View::partial('list')` resolves to `plugins/<slug>/views/list.php`.

### `install.sql`

Optional. Each `;`-separated statement runs once, when the plugin is installed.
Use `CREATE TABLE IF NOT EXISTS` so re-installs are safe.

---

## 2. Lifecycle

Managed from **Settings → Plugins** (or programmatically via
`Niyanta\Core\PluginManager`):

| Action       | What happens                                                        |
|--------------|---------------------------------------------------------------------|
| **Upload**   | A ZIP (one top-level folder) is extracted into `/plugins` (path-traversal checked). |
| **Install**  | `install.sql` runs; a row is added to the `plugins` table.          |
| **Activate** | `is_active = 1`; `index.php` is loaded on every request.            |
| **Deactivate** | `is_active = 0`; the plugin stops loading.                        |
| **Remove**   | DB row deleted and the plugin folder removed.                       |

---

## 3. Registries (the extension API)

All live under the `Niyanta\Core` namespace.

### Router
```php
Router::get(string $path, $handler, array $options = []);
Router::post(string $path, $handler, array $options = []);
// $handler: [Controller::class, 'method'] or a closure
// $options: ['auth' => true|false, 'permission' => 'slug'|null]
```
POST routes are automatically CSRF-checked.

### Menu
```php
Menu::add(['label' => '', 'route' => '', 'icon' => 'bi-…', 'permission' => null, 'order' => 100]);
```

### Permissions
```php
Permissions::register('slug', 'description', ['manager', 'employee']); // declare + attach
Permissions::can('slug');      // check current user
Permissions::require('slug');  // 403 if missing
```
Super Admin holds the `*` wildcard, so new permissions work immediately for
admins. Custom roles created in the database can be granted any registered
permission — **the permission system supports custom roles/permissions with no
core changes.**

### Dashboard
```php
Dashboard::add([
    'key' => 'unique_key', 'title' => '', 'icon' => 'bi-…', 'order' => 100,
    'permission' => null,
    'render' => fn(): string => '<strong>42</strong>',
]);
```
Registering a widget with an existing `key` replaces it.

### View
```php
View::partial('template', ['x' => 1]);          // render to string
View::render('template', $data, 'app');          // render inside a layout
View::addPath('/abs/dir');                       // add a view search path
```
Layouts available to plugins: `app` (authenticated shell) and `auth`.

---

## 4. Hooks & events

WordPress-style actions and filters let plugins react to and reshape core
behaviour.

```php
// Actions — fire-and-forget
Hooks::addAction('after_login', function (array $user): void { /* … */ });
Hooks::doAction('after_login', $user);

// Filters — transform a value
Hooks::addFilter('menu_items', fn(array $items) => $items);
$items = Hooks::applyFilters('menu_items', $items);
```

### Core hooks shipped today

| Hook                  | Type   | Payload            | When                              |
|-----------------------|--------|--------------------|-----------------------------------|
| `after_login`         | action | `$user`            | After a successful sign-in        |
| `before_logout`       | action | `$user`            | Before the session is destroyed   |
| `plugin_installed`    | action | `$slug`            | After install.sql runs            |
| `plugin_activated`    | action | `$slug`            | On activation                     |
| `plugin_deactivated`  | action | `$slug`            | On deactivation                   |
| `plugin_removed`      | action | `$slug`            | On removal                        |
| `plugin_uploaded`     | action | `$slug`            | After a ZIP is extracted          |
| `menu_items`          | filter | `array $items`     | Building the sidebar              |
| `dashboard_widgets`   | filter | `array $widgets`   | Building the dashboard            |

New hooks are added by calling `Hooks::doAction()` / `Hooks::applyFilters()`
anywhere — including from your own plugin, so plugins can extend each other.

---

## 5. Database access

```php
use Niyanta\Core\Database;

Database::all('SELECT * FROM employees WHERE dept_id = ?', [$id]);
Database::first('SELECT * FROM employees WHERE id = ?', [$id]);
Database::scalar('SELECT COUNT(*) FROM employees');
Database::insert('INSERT INTO employees (name) VALUES (?)', [$name]); // returns id
Database::query('UPDATE employees SET name = ? WHERE id = ?', [$name, $id]);
```
Always use parameter placeholders — never interpolate user input.

---

## 6. Checklist for a new plugin

1. Create `/plugins/<slug>/plugin.json`.
2. Add `index.php` returning your `register()` closure.
3. (Optional) Add `install.sql`, `views/`, `controllers/`, `assets/`.
4. Declare permissions with `Permissions::register()`.
5. Register routes, menu items and widgets.
6. ZIP the folder (single top-level directory) and upload via Settings →
   Plugins, **or** drop the folder into `/plugins` directly, then Install →
   Activate.

That's it — no core edits, ever.
