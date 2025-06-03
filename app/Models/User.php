<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser; // <-- Tambahkan ini
use Filament\Panel; // <-- Tambahkan ini
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // Sudah ada sebelumnya
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable implements FilamentUser // <-- Implementasikan FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'region_id',      // Kolom kustom WGS
        'wgs_job_title',  // Kolom kustom WGS
        'wgs_level',      // Kolom kustom WGS
        'email_verified_at', // Untuk Filament user awal
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Pastikan password di-hash otomatis saat di-set
    ];

    // Implementasi method dari FilamentUser
    public function canAccessPanel(Panel $panel): bool
    {
        // Logika untuk menentukan apakah user bisa mengakses panel tertentu.
        // Untuk sekarang, semua user yang terautentikasi bisa.
        // Anda bisa menambahkan logika berdasarkan role di sini, misal:
        // return $this->hasRole(['Tim IT', 'Admin Cabang', 'Kepala Cabang', ...]); 
        // atau return str_ends_with($this->email, '@wgs.com') && $this->hasVerifiedEmail();
        return true;
    }

    // Relasi ke Region
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
