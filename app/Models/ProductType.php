<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'required_documents',
        'escalation_threshold',
        'payment_simulation_image', // Tambahkan ini
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'required_documents' => 'array',
        'escalation_threshold' => 'decimal:2',
    ];

    /**
     * Mendefinisikan relasi bahwa satu ProductType memiliki banyak ProductTypeRule.
     * Ini adalah method yang hilang.
     */
    public function productTypeRules(): HasMany
    {
        return $this->hasMany(ProductTypeRule::class);
    }
}
