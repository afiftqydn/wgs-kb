<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    protected $fillable = [
        'judul',
        'deskripsi',
        'kategori',
        'dokumen_path',
        'gambar_path',
    ];
}
