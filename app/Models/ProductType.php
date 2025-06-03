<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject; // Atau AsCollection

class ProductType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'required_documents' => AsArrayObject::class, // Ini penting!
        'escalation_threshold' => 'decimal:2',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'required_documents' => AsArrayObject::class, // Otomatis cast JSON ke ArrayObject dan sebaliknya
        'escalation_threshold' => 'decimal:2',
    ];
}
