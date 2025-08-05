<?php

namespace App\Observers;

use App\Models\PomigorStockMovement;
use App\Models\PomigorDepot;

class PomigorStockMovementObserver
{
    public function created(PomigorStockMovement $movement): void
    {
        self::updateDepotStock($movement->pomigor_depot_id);
    }

    public function updated(PomigorStockMovement $movement): void
    {
        self::updateDepotStock($movement->pomigor_depot_id);
    }

    public function deleted(PomigorStockMovement $movement): void
    {
        self::updateDepotStock($movement->pomigor_depot_id);
    }

    // Jika soft deletes digunakan
    public function restored(PomigorStockMovement $movement): void
    {
        self::updateDepotStock($movement->pomigor_depot_id);
    }

    protected static function updateDepotStock(int $depotId): void
    {
        $depot = PomigorDepot::find($depotId);
        if (! $depot) return;

        $stock = PomigorStockMovement::where('pomigor_depot_id', $depotId)
            ->get()
            ->sum(function ($movement) {
                return match ($movement->transaction_type) {
                    'REFILL', 'ADJUSTMENT_INCREASE' => $movement->quantity_liters,
                    'SALE_REPORTED', 'ADJUSTMENT_DECREASE' => -$movement->quantity_liters,
                    default => 0,
                };
            });

        $depot->update(['current_stock_liters' => $stock]);
    }
}
