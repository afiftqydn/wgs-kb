<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Notifications\ApplicationAssignedNotification;

class LoanApplication extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount_requested' => 'decimal:2',
    ];

    /**
     * Properti virtual untuk menyimpan catatan workflow sementara sebelum disimpan.
     */
    public ?string $workflow_notes = null;

    // --- ACTIVITY LOG ---
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'application_number', 
                'customer_id', 
                'product_type_id', 
                'amount_requested', 
                'status', 
                'assigned_to'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Permohonan dengan nomor '{$this->application_number}' telah {$eventName}")
            ->useLogName('LoanApplication');
    }

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
            // 1. Isi created_by
            if (Auth::check() && !$application->created_by) {
                $application->created_by = Auth::id();
            }

            // 2. Isi input_region_id
            if (empty($application->input_region_id) && Auth::check() && Auth::user()->region_id) {
                $application->input_region_id = Auth::user()->region_id;
            }
            
            // 3. Isi processing_region_id awal
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

            // 4. Generate Application Number
            if (empty($application->application_number)) {
                $yearMonth = date('Y/m');
                $prefix = 'APP/' . $yearMonth . '/';
                $lastApp = self::where('application_number', 'like', $prefix . '%')->orderBy('application_number', 'desc')->first();
                $nextNumber = 1;
                if ($lastApp) {
                    $parts = explode('/', $lastApp->application_number);
                    if (is_numeric(end($parts))) { $nextNumber = intval(end($parts)) + 1; }
                }
                $application->application_number = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }

            // 5. Logika Penugasan Otomatis Awal (Dipindahkan ke 'creating')
            if ($application->status === 'SUBMITTED' && is_null($application->assigned_to)) {
                 if ($application->processing_region_id) {
                    $regionCheck = Region::find($application->processing_region_id);
                    if ($regionCheck && $regionCheck->type === 'UNIT') {
                        $adminUnitUser = User::where('region_id', $regionCheck->id)
                                             ->whereHas('roles', fn ($query) => $query->where('name', 'Admin Unit'))
                                             ->first();
                        if ($adminUnitUser) {
                            $application->assigned_to = $adminUnitUser->id;
                        } else {
                            $kepalaUnitUser = User::where('region_id', $regionCheck->id)
                                                 ->whereHas('roles', fn ($query) => $query->where('name', 'Kepala Unit'))
                                                 ->first();
                            if ($kepalaUnitUser) {
                                 $application->assigned_to = $kepalaUnitUser->id;
                            }
                        }
                    }
                }
            }
        });

        static::created(function ($application) {
            // Catat workflow awal setelah record benar-benar dibuat
            ApplicationWorkflow::create([
                'loan_application_id' => $application->id,
                'from_status' => null,
                'to_status' => $application->status,
                'processed_by' => $application->created_by ?? (Auth::check() ? Auth::id() : null),
                'notes' => $application->workflow_notes ?? 'Permohonan dibuat dengan status awal ' . $application->status . '.',
            ]);
            $application->workflow_notes = null;

            // Kirim notifikasi jika 'assigned_to' berhasil diisi saat pembuatan
            if (!is_null($application->assigned_to)) {
                $newAssignee = User::find($application->assigned_to);
                if ($newAssignee) {
                     $assigner = $application->creator ?? (Auth::check() ? Auth::user() : null);
                     $newAssignee->notify(new ApplicationAssignedNotification($application, $assigner));
                }
            }
        });
        
        static::updating(function ($application) {
            $userPerformingAction = Auth::check() ? Auth::user() : null;

            // Catat workflow jika status berubah
            if ($application->isDirty('status')) {
                ApplicationWorkflow::create([
                    'loan_application_id' => $application->id,
                    'from_status' => $application->getOriginal('status'),
                    'to_status' => $application->status,
                    'processed_by' => $userPerformingAction ? $userPerformingAction->id : ($application->getOriginal('assigned_to') ?? $application->creator->id ?? null),
                    'notes' => $application->workflow_notes ?? 'Status permohonan diubah.',
                ]);
                $application->workflow_notes = null;
            }

            // Kirim notifikasi jika 'assigned_to' berubah
            if ($application->isDirty('assigned_to') && !is_null($application->assigned_to) && $application->assigned_to != $application->getOriginal('assigned_to')) {
                $newAssignee = User::find($application->assigned_to);
                if ($newAssignee) {
                    $assigner = $userPerformingAction ?? User::find($application->getOriginal('assigned_to')) ?? $application->creator;
                    $newAssignee->notify(new ApplicationAssignedNotification($application, $assigner));
                }
            }
        });
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