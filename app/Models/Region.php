<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // Relasi untuk hierarki (self-reference)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    // Relasi ke Users
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Relasi ke Referrers
    public function referrers(): HasMany
    {
        return $this->hasMany(Referrer::class);
    }
}
