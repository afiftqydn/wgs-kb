<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth; // <-- Pastikan Auth di-import

class Arsip extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * DIUBAH: Sesuaikan dengan nama kolom di file migrasi.
     */
    protected $fillable = [
        'kode_arsip',
        'nama_arsip',
        'kategori',
        'tanggal_dokumen',
        'keterangan',
        'customer_id',             // <-- DIUBAH
        'loan_application_id',     // <-- DIUBAH
        'file_path',
        'lokasi_fisik',
        'status',
        'tanggal_retensi',
        'created_by',
    ];

    protected $casts = [
        'tanggal_dokumen' => 'date',
        'tanggal_retensi' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($arsip) {
            if (Auth::check()) {
                $arsip->created_by = Auth::id();
            }
            $latestArsip = self::withTrashed()->latest('id')->first();
            $nextId = $latestArsip ? $latestArsip->id + 1 : 1;
            $arsip->kode_arsip = 'ARSIP/' . now()->year . '/' . str_pad(now()->month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }

    /**
     * DIUBAH: Nama fungsi relasi dan foreign key disesuaikan.
     */
    public function customer(): BelongsTo // <-- DIUBAH: nama fungsi menjadi customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id'); // <-- DIUBAH: foreign key
    }

    /**
     * DIUBAH: Nama fungsi relasi dan foreign key disesuaikan.
     */
    public function loanApplication(): BelongsTo // <-- DIUBAH: nama fungsi menjadi loanApplication()
    {
        // Model LoanApplication sesuai nama file Anda
        return $this->belongsTo(LoanApplication::class, 'loan_application_id'); // <-- DIUBAH: foreign key
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}