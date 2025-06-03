<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth; // Import Auth facade

class LoanApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number',
        'customer_id',
        'product_type_id',
        'amount_requested',
        'purpose',
        'input_region_id',
        'processing_region_id',
        'status',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
    ];

    // --- RELASI ---
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function inputRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'input_region_id');
    }

    public function processingRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'processing_region_id');
    }

    public function creator(): BelongsTo // User yang input
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo // User yang ditugaskan
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function documents(): HasMany // Dokumen pendukung
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function workflows(): HasMany // Log alur kerja (akan digunakan di Sprint 3)
    {
        return $this->hasMany(ApplicationWorkflow::class);
    }

    // --- MODEL EVENTS ---
    protected static function booted()
    {
        static::creating(function ($application) {
            // Isi created_by dengan user yang sedang login jika belum ada
            if (Auth::check() && !$application->created_by) {
                $application->created_by = Auth::id();
            }

            // Isi input_region_id dengan region user yang login jika belum ada dan user punya region_id
            if (empty($application->input_region_id) && Auth::check() && Auth::user()->region_id) {
                $application->input_region_id = Auth::user()->region_id;
            }

            // Awalnya, processing region bisa sama dengan input region jika belum ada logika khusus
            // atau jika input_region sudah merupakan UNIT.
            if (empty($application->processing_region_id) && $application->input_region_id) {
                $inputRegion = Region::find($application->input_region_id);
                if ($inputRegion && $inputRegion->type === 'UNIT') {
                    $application->processing_region_id = $application->input_region_id;
                } elseif ($inputRegion && $inputRegion->type === 'SUBUNIT' && $inputRegion->parent_id) {
                    // Jika input adalah SUBUNIT, maka processing_region_id adalah parent UNIT nya
                    $application->processing_region_id = $inputRegion->parent_id;
                }
                // Jika input_region adalah CABANG, processing_region_id bisa tetap Cabang atau null
                // tergantung alur bisnis yang diinginkan.
            }


            // Generate Application Number (Contoh: APP/YYYY/MM/00001)
            if (empty($application->application_number)) {
                $yearMonth = date('Y/m');
                $prefix = 'APP/' . $yearMonth . '/';

                // Cari nomor urut terakhir untuk bulan dan tahun ini
                $lastApp = LoanApplication::where('application_number', 'like', $prefix . '%')
                    ->orderBy('application_number', 'desc') // Urutkan berdasarkan string nomor aplikasi
                    ->first();
                $nextNumber = 1;
                if ($lastApp) {
                    $parts = explode('/', $lastApp->application_number);
                    $lastSequence = end($parts);
                    if (is_numeric($lastSequence)) {
                        $nextNumber = intval($lastSequence) + 1;
                    }
                }
                $application->application_number = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function ($application) {
            // Logika penugasan otomatis saat status diubah menjadi SUBMITTED
            if ($application->isDirty('status') && $application->status === 'SUBMITTED') {

                $targetProcessingRegion = null; // Ini harus berupa ID Region UNIT

                if ($application->processing_region_id) {
                    // Jika processing_region_id sudah diisi (dan merupakan UNIT), gunakan itu
                    $regionCheck = Region::find($application->processing_region_id);
                    if ($regionCheck && $regionCheck->type === 'UNIT') {
                        $targetProcessingRegion = $regionCheck;
                    }
                } elseif ($application->input_region_id) {
                    // Jika processing_region_id kosong, coba tentukan dari input_region_id
                    $inputRegion = Region::find($application->input_region_id);
                    if ($inputRegion) {
                        if ($inputRegion->type === 'UNIT') {
                            $targetProcessingRegion = $inputRegion;
                        } elseif ($inputRegion->type === 'SUBUNIT' && $inputRegion->parent_id) {
                            $parentUnit = Region::find($inputRegion->parent_id);
                            if ($parentUnit && $parentUnit->type === 'UNIT') {
                                $targetProcessingRegion = $parentUnit;
                            }
                        }
                    }
                }

                // Jika targetProcessingRegion (UNIT) berhasil ditentukan
                if ($targetProcessingRegion) {
                    $application->processing_region_id = $targetProcessingRegion->id; // Pastikan processing_region_id diset ke UNIT yang benar

                    // Cari user dengan peran "Admin Unit" di targetProcessingRegion tersebut
                    $adminUnitUser = User::where('region_id', $targetProcessingRegion->id)
                        ->whereHas('roles', function ($query) {
                            $query->where('name', 'Admin Unit'); // Nama role Spatie
                        })->first();

                    if ($adminUnitUser) {
                        $application->assigned_to = $adminUnitUser->id;
                    } else {
                        // Logika fallback: Jika Admin Unit tidak ditemukan di Unit tersebut.
                        // Bisa ditugaskan ke Kepala Unit, atau notifikasi ke Admin Cabang.
                        // Atau biarkan assigned_to null dan memerlukan intervensi manual.
                        // Contoh: cari Kepala Unit di Unit yang sama
                        $kepalaUnitUser = User::where('region_id', $targetProcessingRegion->id)
                            ->whereHas('roles', function ($query) {
                                $query->where('name', 'Kepala Unit');
                            })->first();
                        if ($kepalaUnitUser) {
                            $application->assigned_to = $kepalaUnitUser->id;
                        } else {
                            // $application->assigned_to = null; // Atau tindakan lain
                        }
                        // Anda mungkin ingin menambahkan log atau notifikasi di sini
                    }
                } else {
                    // Logika jika Unit Pemroses tidak bisa ditentukan (misal, input dari Cabang langsung, atau data region tidak lengkap)
                    // Mungkin perlu ditugaskan ke peran tertentu di Cabang atau memerlukan intervensi manual.
                    // $application->assigned_to = null; // Atau tindakan lain
                }
            }
        });
    }
}
