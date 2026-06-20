<?php
namespace Niyanta\Core;

/**
 * Sidebar menu registry. Core and plugins register menu items here; the
 * sidebar partial renders whatever the current user is permitted to see.
 */
class Menu
{
    /** @var array<int, array{label:string,route:string,icon:string,permission:?string,order:int}> */
    private static array $items = [];

    /**
     * @param array{label:string,route:string,icon?:string,permission?:?string,order?:int} $item
     */
    public static function add(array $item): void
    {
        self::$items[] = [
            'label'      => $item['label'],
            'route'      => $item['route'],
            'icon'       => $item['icon'] ?? 'bi-dot',
            'permission' => $item['permission'] ?? null,
            'order'      => $item['order'] ?? 100,
        ];
    }

    /** Menu items visible to the current user, sorted, after filters. */
    public static function visible(): array
    {
        $items = Hooks::applyFilters('menu_items', self::$items);
        $items = array_filter($items, static function ($item) {
            return empty($item['permission']) || Permissions::can($item['permission']);
        });
        usort($items, static fn($a, $b) => $a['order'] <=> $b['order']);
        return $items;
    }
}
