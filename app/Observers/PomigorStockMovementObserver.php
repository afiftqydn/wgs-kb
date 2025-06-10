<?php

namespace App\Observers;

use App\Models\PomigorStockMovement;
use App\Models\PomigorDepot; // Import PomigorDepot

class PomigorStockMovementObserver
{
    /**
     * Handle the PomigorStockMovement "created" event.
     */
    public function created(PomigorStockMovement $pomigorStockMovement): void
    {
        $this->updateDepotStock($pomigorStockMovement->pomigorDepot);
    }

    /**
     * Handle the PomigorStockMovement "updated" event.
     */
    public function updated(PomigorStockMovement $pomigorStockMovement): void
    {
        // Jika quantity atau tipe transaksi berubah, perlu update stok
        // Atau selalu update untuk menyederhanakan jika ada perubahan
        $this->updateDepotStock($pomigorStockMovement->pomigorDepot);
    }

    /**
     * Handle the PomigorStockMovement "deleted" event.
     */
    public function deleted(PomigorStockMovement $pomigorStockMovement): void
    {
        $this->updateDepotStock($pomigorStockMovement->pomigorDepot);
    }

    /**
    * Recalculate and update the current stock for the given depot.
    */
    protected function updateDepotStock(PomigorDepot $depot): void
    {
        $stockIn = $depot->stockMovements()
                        ->whereIn('transaction_type', ['REFILL', 'ADJUSTMENT_INCREASE'])
                        ->sum('quantity_liters');

        $stockOut = $depot->stockMovements()
                         ->whereIn('transaction_type', ['SALE_REPORTED', 'ADJUSTMENT_DECREASE'])
                         ->sum('quantity_liters');

        $depot->current_stock_liters = $stockIn - $stockOut;
        $depot->saveQuietly(); // saveQuietly() untuk mencegah trigger event update lagi pada PomigorDepot jika ada observer di sana
    }
}