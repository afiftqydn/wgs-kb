<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_lengkap',
        'jabatan',
        'email',
        'no_hp',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat_ktp',
        'alamat_domisili',
        'status_pernikahan',
        'agama',
        'region_id', // Ubah 'kantor' menjadi 'region_id'
        'status_karyawan',
        'tanggal_bergabung',
        'tanggal_berakhir_kontrak',
        'npwp',
        'bpjs_ketenagakerjaan',
        'bpjs_kesehatan',
        'nama_bank',
        'nomor_rekening',
        'nama_pemilik_rekening',
        'nama_kontak_darurat',
        'hubungan_kontak_darurat',
        'no_hp_kontak_darurat',
        'pas_foto',
        'file_ktp',
        'file_npwp',
        'file_perjanjian_kerja',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
        'tanggal_berakhir_kontrak' => 'date',
        'jenis_kelamin' => 'string',
        'status_pernikahan' => 'string',
        'agama' => 'string',
        'status_karyawan' => 'string',
    ];

    /**
     * Get the region that owns the Karyawan.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}