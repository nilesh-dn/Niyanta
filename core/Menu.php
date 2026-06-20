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
     * @param array{label:string,route:string,icon?:string,permission?:?string,permission_any?:string[],order?:int} $item
     */
    public static function add(array $item): void
    {
        self::$items[] = [
            'label'          => $item['label'],
            'route'          => $item['route'],
            'icon'           => $item['icon'] ?? 'bi-dot',
            'permission'     => $item['permission'] ?? null,
            'permission_any' => $item['permission_any'] ?? [],
            'order'          => $item['order'] ?? 100,
        ];
    }

    /** Menu items visible to the current user, sorted, after filters. */
    public static function visible(): array
    {
        $items = Hooks::applyFilters('menu_items', self::$items);
        $items = array_filter($items, static function ($item) {
            // Single required permission (if set).
            if (!empty($item['permission']) && !Permissions::can($item['permission'])) {
                return false;
            }
            // "Any of" permissions: visible if the user holds at least one.
            if (!empty($item['permission_any'])) {
                foreach ($item['permission_any'] as $perm) {
                    if (Permissions::can($perm)) {
                        return true;
                    }
                }
                return false;
            }
            return true;
        });
        usort($items, static fn($a, $b) => $a['order'] <=> $b['order']);
        return $items;
    }
}
