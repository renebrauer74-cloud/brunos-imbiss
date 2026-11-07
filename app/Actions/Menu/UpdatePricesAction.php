<?php

namespace App\Actions\Menu;

use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

/**
 * Speichert die Preise aus der Speisekarten-Übersicht (Schnell-Bearbeitung).
 */
class UpdatePricesAction
{
    public function __construct(private ParsePriceAction $parsePrice) {}

    /**
     * @param  array<int|string, mixed>  $prices  [menu_item_id => "3,50"]
     * @return array{changed: int, invalid: int}
     */
    public function execute(array $prices): array
    {
        $changed = 0;
        $invalid = 0;

        DB::transaction(function () use ($prices, &$changed, &$invalid): void {
            $items = MenuItem::query()->whereKey(array_keys($prices))->get()->keyBy('id');

            foreach ($prices as $id => $value) {
                $cents = is_string($value) ? $this->parsePrice->execute($value) : null;
                $item = $items->get((int) $id);

                if ($cents === null) {
                    $invalid++;

                    continue;
                }

                if ($item === null || $item->price_cents === $cents) {
                    continue;
                }

                $item->update(['price_cents' => $cents]);
                $changed++;
            }
        });

        return ['changed' => $changed, 'invalid' => $invalid];
    }
}
