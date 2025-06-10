<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth; // Pastikan Auth diimport
use Illuminate\Support\Facades\DB; // Untuk transaksi database jika diperlukan

class PomigorDepot extends Model
{
    use HasFactory;

    protected $fillable = [
        'depot_code', // Meskipun di-generate, tetap di fillable agar bisa diset jika ada kasus khusus (jarang)
        'name',
        'region_id',
        'customer_id',
        'address',
        'latitude',
        'longitude',
        'current_stock_liters',
        'status',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'current_stock_liters' => 'decimal:2',
    ];

    // ... (Relasi-relasi yang sudah ada: region(), customer(), creator(), stockMovements())
    public function region(): BelongsTo { return $this->belongsTo(Region::class, 'region_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function stockMovements(): HasMany { return $this->hasMany(PomigorStockMovement::class); }
        
    protected static function booted() // Gunakan booted() untuk model events
    {
        parent::boot(); // Panggil boot parent jika ada

        static::creating(function ($depot) {
            // Isi created_by otomatis
            if (Auth::check() && !$depot->created_by) {
                $depot->created_by = Auth::id();
            }

            // Generate depot_code otomatis jika belum ada
            if (empty($depot->depot_code) && $depot->region_id) {
                $region = Region::find($depot->region_id);
                $regionCode = $region ? $region->code : 'XXXX'; // Ambil kode region (UNIT)

                // Cari nomor urut terakhir untuk region ini
                // Format: PGR-REGIONCODE-NNN
                $prefixForQuery = 'PGR-' . $regionCode . '-';
                $lastDepot = PomigorDepot::where('region_id', $depot->region_id)
                                         ->where('depot_code', 'like', $prefixForQuery . '%')
                                         ->orderBy('depot_code', 'desc') // Urutkan berdasarkan depot_code untuk mendapatkan yang terbaru
                                         ->first();
                
                $nextSequence = 1;
                if ($lastDepot) {
                    // Ekstrak nomor urut dari kode depot terakhir
                    $parts = explode('-', $lastDepot->depot_code);
                    $lastSequence = end($parts);
                    if (is_numeric($lastSequence)) {
                        $nextSequence = intval($lastSequence) + 1;
                    }
                }
                
                $depot->depot_code = $prefixForQuery . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}