<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Tambahkan ini
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'type' => 'string',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function karyawans(): HasMany
    {
        return $this->hasMany(Karyawan::class);
    }
}