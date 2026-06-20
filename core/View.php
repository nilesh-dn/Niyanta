<?php
namespace Niyanta\Core;

/**
 * Minimal PHP template renderer with layouts and partials. Views are plain
 * PHP files under /views (and plugin view directories can be added).
 */
class View
{
    /** @var string[] Additional directories to search for views. */
    private static array $paths = [];

    public static function addPath(string $dir): void
    {
        self::$paths[] = rtrim($dir, '/');
    }

    private static function resolve(string $template): string
    {
        $candidates = array_merge([base_path('views')], self::$paths);
        foreach ($candidates as $dir) {
            $file = $dir . '/' . str_replace('.', '/', $template) . '.php';
            if (is_file($file)) {
                return $file;
            }
        }
        throw new \RuntimeException("View not found: {$template}");
    }

    /** Render a partial/view to a string. */
    public static function partial(string $template, array $data = []): string
    {
        $file = self::resolve($template);
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }

    /**
     * Render a view, optionally wrapped in a layout. The view output is made
     * available to the layout as $content.
     */
    public static function render(string $template, array $data = [], ?string $layout = null): void
    {
        $content = self::partial($template, $data);
        if ($layout === null) {
            echo $content;
            return;
        }
        echo self::partial('layouts.' . $layout, array_merge($data, ['content' => $content]));
    }
}
