<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'required_documents',
        'escalation_threshold',
        'payment_simulation_image',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'required_documents' => 'array',
        'escalation_threshold' => 'decimal:2',
    ];

    public function productTypeRules(): HasMany
    {
        return $this->hasMany(ProductTypeRule::class);
    }

    /**
     * [DITAMBAHKAN] Relasi ke LoanApplication.
     * Baris ini penting agar model ProductType bisa mengecek data LoanApplication yang terkait.
     * Pastikan namespace \App\Models\LoanApplication::class sudah benar.
     */
    public function loanApplications(): HasMany
    {
        return $this->hasMany(\App\Models\LoanApplication::class);
    }
}