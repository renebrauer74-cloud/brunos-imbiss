<?php

namespace App\Actions\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Schiebt einen Eintrag innerhalb einer sortierten Liste eine Position nach vorne (-1) oder hinten (+1)
 * und nummeriert die Liste dabei in Zehnerschritten neu durch.
 */
class MoveInOrderAction
{
    /**
     * @param  Collection<int, Model>  $ordered  alle Einträge der Liste in der aktuellen Reihenfolge
     */
    public function execute(Collection $ordered, Model $entry, int $direction, string $column = 'sort_order'): void
    {
        $entries = $ordered->values()->all();
        $position = collect($entries)->search(fn (Model $candidate): bool => $candidate->is($entry));
        $target = $position === false ? null : $position + ($direction < 0 ? -1 : 1);

        if ($target === null || $target < 0 || $target >= count($entries)) {
            return;
        }

        [$entries[$position], $entries[$target]] = [$entries[$target], $entries[$position]];

        DB::transaction(function () use ($entries, $column): void {
            foreach ($entries as $index => $model) {
                $model->forceFill([$column => ($index + 1) * 10])->save();
            }
        });
    }
}
