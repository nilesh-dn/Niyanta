<?php
namespace Niyanta\Core;

/**
 * Dashboard widget registry. Core registers placeholder widgets; plugins add
 * or replace widgets via Dashboard::add() or the 'dashboard_widgets' filter.
 */
class Dashboard
{
    /**
     * @var array<int, array{
     *   key:string, title:string, icon:string, permission:?string,
     *   order:int, render:callable
     * }>
     */
    private static array $widgets = [];

    /**
     * @param array{
     *   key:string, title:string, icon?:string, permission?:?string,
     *   order?:int, render:callable
     * } $widget
     */
    public static function add(array $widget): void
    {
        self::$widgets[$widget['key']] = [
            'key'        => $widget['key'],
            'title'      => $widget['title'],
            'icon'       => $widget['icon'] ?? 'bi-grid',
            'permission' => $widget['permission'] ?? null,
            'order'      => $widget['order'] ?? 100,
            'render'     => $widget['render'],
        ];
    }

    /** Widgets visible to the current user, sorted, after filters. */
    public static function visible(): array
    {
        $widgets = Hooks::applyFilters('dashboard_widgets', array_values(self::$widgets));
        $widgets = array_filter($widgets, static function ($widget) {
            return empty($widget['permission']) || Permissions::can($widget['permission']);
        });
        usort($widgets, static fn($a, $b) => $a['order'] <=> $b['order']);
        return $widgets;
    }
}
