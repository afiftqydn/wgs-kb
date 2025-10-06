<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MigrasiData extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'migrasi_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_nasabah',
        'nama_ibu_kandung',
        'alamat',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'identitas_nasabah',
        'nik',
        'agama',
        'desa',
        'kecamatan',
        'kota_kabupaten',
        'provinsi',
        'no_hp',
        'tanggal_register',
        'simpok',
        'simwajib',
    ];
}