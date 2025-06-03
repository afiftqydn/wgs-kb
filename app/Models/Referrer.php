<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referrer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'region_id',
        'unique_person_organization_code',
        'generated_referral_code',
        'contact_person',
        'phone',
        'status',
    ];

    // Relasi ke Region
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
