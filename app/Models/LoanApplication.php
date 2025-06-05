<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity; // <-- Import Trait
use Spatie\Activitylog\LogOptions;          // <-- Import LogOptions
use App\Notifications\ApplicationAssignedNotification; // Pastikan ini diimport

class LoanApplication extends Model
{
    use HasFactory, LogsActivity;

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


    /**
     * Properti virtual untuk menyimpan catatan workflow sementara sebelum disimpan.
     * Akan digunakan oleh event 'updating' atau 'created' untuk mencatat notes.
     */
    public ?string $workflow_notes = null;

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

    public function workflows(): HasMany // Log alur kerja
    {
        return $this->hasMany(ApplicationWorkflow::class);
    }

    // --- MODEL EVENTS ---
    protected static function booted()
    {
        static::creating(function ($application) {
            // 1. Isi created_by dengan user yang sedang login jika belum ada
            if (Auth::check() && !$application->created_by) {
                $application->created_by = Auth::id();
            }

            // 2. Isi input_region_id dengan region user yang login jika belum ada dan user punya region_id
            if (empty($application->input_region_id) && Auth::check() && Auth::user()->region_id) {
                $application->input_region_id = Auth::user()->region_id;
            }
            
            // 3. Isi processing_region_id awal berdasarkan input_region_id
            if (empty($application->processing_region_id) && $application->input_region_id) {
                $inputRegion = Region::find($application->input_region_id);
                if ($inputRegion) {
                    if ($inputRegion->type === 'UNIT') {
                        $application->processing_region_id = $inputRegion->id;
                    } elseif ($inputRegion->type === 'SUBUNIT' && $inputRegion->parent_id) {
                        $parentUnit = Region::find($inputRegion->parent_id);
                        if ($parentUnit && $parentUnit->type === 'UNIT') {
                            $application->processing_region_id = $parentUnit->id;
                        }
                    }
                }
            }

            // 4. Generate Application Number (Contoh: APP/YYYY/MM/00001)
            if (empty($application->application_number)) {
                $yearMonth = date('Y/m');
                $prefix = 'APP/' . $yearMonth . '/';
                
                $lastApp = LoanApplication::where('application_number', 'like', $prefix . '%')
                                          ->orderBy('application_number', 'desc')
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

        static::created(function ($application) {
            // Catat workflow saat pertama kali dibuat
            ApplicationWorkflow::create([
                'loan_application_id' => $application->id,
                'from_status' => null,
                'to_status' => $application->status,
                'processed_by' => $application->created_by ?? (Auth::check() ? Auth::id() : null),
                'notes' => $application->workflow_notes ?? 'Permohonan dibuat dengan status awal ' . $application->status . '.',
            ]);
            $application->workflow_notes = null; // Reset catatan virtual

            // Jika status awal adalah SUBMITTED dan belum ada yang ditugaskan, coba tugaskan
            if ($application->status === 'SUBMITTED' && is_null($application->assigned_to)) {
                self::assignToRelevantUserOnSubmit($application);
                // Jika assignToRelevantUserOnSubmit mengubah assigned_to, notifikasi akan dikirim oleh event updating (jika assigned_to berbeda dari null awal)
                // atau kita bisa kirim notifikasi di sini secara eksplisit jika perlu (tapi bisa duplikat jika assigned_to juga di-trigger di updating)
                // Untuk menghindari duplikasi, kita andalkan event 'updating' atau logika di dalam 'assignToRelevantUserOnSubmit' untuk notifikasi jika ada perubahan aktual.
                // Namun, karena ini 'created', 'isDirty' tidak berlaku. Kita bisa langsung notifikasi jika assigned_to berhasil diset.
                if(!is_null($application->fresh()->assigned_to)) { // Ambil data terbaru setelah assignToRelevantUserOnSubmit mungkin mengubahnya
                    $newAssignee = User::find($application->fresh()->assigned_to);
                    if ($newAssignee) {
                         $assigner = Auth::check() ? Auth::user() : ($application->creator ?? null);
                         $newAssignee->notify(new ApplicationAssignedNotification($application->fresh(), $assigner));
                    }
                }
            }
        });
        
        static::updating(function ($application) {
            $userPerformingAction = Auth::check() ? Auth::user() : null;

            // A. Catat workflow jika status berubah
            if ($application->isDirty('status')) {
                ApplicationWorkflow::create([
                    'loan_application_id' => $application->id,
                    'from_status' => $application->getOriginal('status'),
                    'to_status' => $application->status,
                    'processed_by' => $userPerformingAction ? $userPerformingAction->id : ($application->getOriginal('assigned_to') ?? $application->creator->id ?? null), // User yang memicu perubahan status
                    'notes' => $application->workflow_notes ?? 'Status permohonan diubah.',
                ]);
                $application->workflow_notes = null; // Reset catatan virtual
            }

            // B. Kirim notifikasi jika 'assigned_to' berubah dan ada assignee baru
            if ($application->isDirty('assigned_to') && !is_null($application->assigned_to) && $application->assigned_to != $application->getOriginal('assigned_to')) {
                $newAssignee = User::find($application->assigned_to);
                if ($newAssignee) {
                    $assigner = $userPerformingAction; // User yang melakukan aksi saat ini
                    // Jika tidak ada user yg login (misal proses sistem), fallback ke yg menugaskan sebelumnya atau creator
                    if (!$assigner && $application->getOriginal('assigned_to')) { 
                        $assigner = User::find($application->getOriginal('assigned_to'));
                    }
                    $assigner = $assigner ?? ($application->creator ?? null);
                    
                    $newAssignee->notify(new ApplicationAssignedNotification($application, $assigner));
                }
            }

            // C. Logika penugasan otomatis jika status diubah menjadi SUBMITTED (misal dari DRAFT) dan belum ada yang ditugaskan
            if ($application->isDirty('status') && $application->status === 'SUBMITTED' && is_null($application->assigned_to)) {
                 self::assignToRelevantUserOnSubmit($application);
                 // Notifikasi akan ditangani oleh blok B jika assigned_to berhasil diubah oleh assignToRelevantUserOnSubmit
            }
        });
    }
    
    /**
     * Helper method untuk logika penugasan saat status SUBMITTED.
     * Method ini akan mencoba mengisi $application->assigned_to.
     */
    protected static function assignToRelevantUserOnSubmit(LoanApplication $application): void
    {
        $targetProcessingUnit = null;

        if ($application->processing_region_id) {
            $regionCheck = Region::find($application->processing_region_id);
            if ($regionCheck && $regionCheck->type === 'UNIT') {
                $targetProcessingUnit = $regionCheck;
            }
        }
        // Jika $targetProcessingUnit masih null, logika di 'creating' seharusnya sudah mencoba mengisinya dengan benar.

        if ($targetProcessingUnit) {
            // Pastikan $application->processing_region_id adalah ID UNIT yang benar
            // (seharusnya sudah benar jika logic di creating event berjalan)
            if ($application->processing_region_id != $targetProcessingUnit->id) {
                 $application->processing_region_id = $targetProcessingUnit->id; // Koreksi jika perlu
            }

            $adminUnitUser = User::where('region_id', $targetProcessingUnit->id)
                                 ->whereHas('roles', fn ($query) => $query->where('name', 'Admin Unit'))
                                 ->first();

            if ($adminUnitUser) {
                $application->assigned_to = $adminUnitUser->id;
            } else {
                $kepalaUnitUser = User::where('region_id', $targetProcessingUnit->id)
                                     ->whereHas('roles', fn ($query) => $query->where('name', 'Kepala Unit'))
                                     ->first();
                if ($kepalaUnitUser) {
                     $application->assigned_to = $kepalaUnitUser->id;
                }
            }
        }
    }
    
    /**
     * Method untuk mengisi catatan workflow sementara dari luar model (misal: Filament Action).
     */
    public function setWorkflowNotes(?string $notes): self
    {
        $this->workflow_notes = $notes;
        return $this;
    }
}