<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity; // <-- Import Trait
use Spatie\Activitylog\LogOptions;          // <-- Import LogOptions


class Customer extends Model
{
    use HasFactory, LogsActivity;

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
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([ // Hanya catat atribut ini
                'application_number', 
                'customer_id', 
                'product_type_id', 
                'amount_requested', 
                'status', 
                'assigned_to'
            ])
            ->logOnlyDirty() // Hanya catat jika atribut yang dilog benar-benar berubah (untuk update)
            ->dontSubmitEmptyLogs() // Jangan buat log jika tidak ada yang berubah atau tidak ada atribut yang dilog
            ->setDescriptionForEvent(fn(string $eventName) => "Permohonan dengan nomor '{$this->application_number}' telah {$eventName}") // Deskripsi log
            ->useLogName('LoanApplication'); // Nama log kustom
    }


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
