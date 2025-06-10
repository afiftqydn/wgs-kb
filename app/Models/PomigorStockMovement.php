<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomigorStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'pomigor_depot_id',
        'transaction_type',
        'quantity_liters',
        'transaction_date',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'quantity_liters' => 'decimal:2',
        'transaction_date' => 'datetime', // atau 'timestamp' jika kolomnya timestamp
    ];

    // Relasi ke PomigorDepot
    public function pomigorDepot(): BelongsTo
    {
        return $this->belongsTo(PomigorDepot::class, 'pomigor_depot_id');
    }

    // Relasi ke User (Admin Unit yang mencatat)
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Otomatis isi recorded_by saat membuat
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($movement) {
            if (Auth()->check() && !$movement->recorded_by) {
                $movement->recorded_by = Auth()->id();
            }
        });

        // Di sini kita akan memanggil logic untuk update current_stock_liters di PomigorDepot
        // setelah record movement dibuat, diupdate, atau dihapus.
        // Ini lebih baik dilakukan menggunakan Observer.
    }
}