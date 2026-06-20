<?php
namespace Niyanta\Core;

/**
 * Front-controller router. Core and plugins register routes; dispatch resolves
 * the current request, enforces authentication and permissions, then invokes
 * the handler.
 *
 *   Router::add('GET', '/dashboard', [DashboardController::class, 'index'], [
 *       'auth' => true, 'permission' => null,
 *   ]);
 *
 * Handlers may be [class, method] pairs or closures.
 */
class Router
{
    /** @var array<int, array{method:string,path:string,handler:mixed,options:array}> */
    private static array $routes = [];

    public static function add(string $method, string $path, $handler, array $options = []): void
    {
        self::$routes[] = [
            'method'  => strtoupper($method),
            'path'    => '/' . trim($path, '/'),
            'handler' => $handler,
            'options' => $options,
        ];
    }

    public static function get(string $path, $handler, array $options = []): void
    {
        self::add('GET', $path, $handler, $options);
    }

    public static function post(string $path, $handler, array $options = []): void
    {
        self::add('POST', $path, $handler, $options);
    }

    /** Determine the current request path, honouring base_path and ?route= fallback. */
    public static function currentPath(): string
    {
        if (isset($_GET['route'])) {
            return '/' . trim((string) $_GET['route'], '/');
        }
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = rtrim((string) Config::get('base_path', ''), '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . trim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    public static function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path   = self::currentPath();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method || $route['path'] !== $path) {
                continue;
            }
            self::handle($route);
            return;
        }

        http_response_code(404);
        View::render('errors/404', ['path' => $path], Auth::check() ? 'app' : 'auth');
    }

    private static function handle(array $route): void
    {
        $options = $route['options'];

        if (($options['auth'] ?? true) === true) {
            Auth::requireLogin();
        }
        if (!empty($options['permission'])) {
            Permissions::require($options['permission']);
        }
        if ($route['method'] === 'POST') {
            Csrf::check();
        }

        $handler = $route['handler'];
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = is_object($class) ? $class : new $class();
            $controller->{$method}();
            return;
        }
        $handler();
    }
}
