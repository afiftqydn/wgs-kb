<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'name',
        'phone',
        'email',
        'address',
        'region_id', // Wilayah domisili nasabah
        'created_by', // User WGS yang input data nasabah
        'referrer_id', // Pihak referrer yang membawa nasabah ini
        'referral_code_used', // Kode referral yang digunakan
    ];

    // Relasi ke Region (wilayah domisili nasabah)
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    // Relasi ke User (yang membuat data nasabah)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke Referrer
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class, 'referrer_id');
    }

    // Relasi ke LoanApplications (satu nasabah bisa punya banyak permohonan)
    // public function loanApplications(): HasMany
    // {
    //     return $this->hasMany(LoanApplication::class);
    // }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($customer) {
            if (Auth::check()) { // Pastikan ada user yang login
                $customer->created_by = Auth::id();
            }
        });
    }
}
