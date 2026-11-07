<?php

namespace App\Actions\Menu;

use App\Models\MenuItem;

/**
 * Gericht zu "Unsere Lieblinge" hinzufügen (kommt ans Ende) oder daraus entfernen.
 */
class SetFeaturedAction
{
    public function execute(MenuItem $item, bool $featured): void
    {
        if ($featured === $item->featured) {
            return;
        }

        $item->featured = $featured;

        if ($featured) {
            $item->featured_order = (int) MenuItem::query()->where('featured', true)->max('featured_order') + 10;
        }

        $item->save();
    }
}
