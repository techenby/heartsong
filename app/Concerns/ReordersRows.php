<?php

namespace App\Concerns;

use Native\Mobile\Edge\Layouts\Builders\NavAction;

/**
 * Native lists can't drag to reorder, so rows get "Move up" / "Move down" menu items instead.
 */
trait ReordersRows
{
    /**
     * @return array<int, NavAction> Items that call $method($id, -1) and $method($id, 1).
     */
    protected function moveMenuItems(string $method, int $id): array
    {
        return [
            NavAction::make("{$method}-up-{$id}")->icon('arrow_upward', ios: 'arrow.up', android: 'arrow_upward')->label('Move up')->press("{$method}({$id}, -1)"),
            NavAction::make("{$method}-down-{$id}")->icon('arrow_downward', ios: 'arrow.down', android: 'arrow_downward')->label('Move down')->press("{$method}({$id}, 1)"),
        ];
    }
}
