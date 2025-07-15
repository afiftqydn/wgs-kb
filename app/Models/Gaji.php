<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gaji extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'periode_bulan',
        'periode_tahun',
        'tanggal_bayar',
        'gaji_pokok',
        'transport',
        'tun_kehadiran',
        'tun_komunikasi',
        'lembur',
        'bpjs',
        'absen',
        'hari_lembur',
        'kas_bon',
        'note',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        // Casting untuk memastikan angka desimal
        'gaji_pokok' => 'decimal:2',
        'transport' => 'decimal:2',
        'tun_kehadiran' => 'decimal:2',
        'tun_komunikasi' => 'decimal:2',
        'lembur' => 'decimal:2',
        'bpjs' => 'decimal:2',
        'absen' => 'decimal:2',
        'kas_bon' => 'decimal:2',
    ];

    /**
     * Relasi ke Karyawan
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Accessor untuk total pendapatan.
     */
    public function getTotalPendapatanAttribute(): float
    {
        return $this->gaji_pokok + $this->transport + $this->tun_kehadiran + $this->tun_komunikasi + $this->lembur;
    }

    /**
     * Accessor untuk total potongan.
     */
    public function getTotalPotonganAttribute(): float
    {
        return $this->bpjs + $this->absen + $this->hari_lembur + $this->kas_bon;
    }

    /**
     * Accessor untuk jumlah yang diterima.
     */
    public function getTotalDiterimaAttribute(): float
    {
        return $this->total_pendapatan - $this->total_potongan;
    }
}