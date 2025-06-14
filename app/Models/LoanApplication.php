<?php

namespace App\Models;

use App\Notifications\ApplicationAssignedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
        'admin_unit_verified_at',
        'analis_verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount_requested' => 'decimal:2',
        'admin_unit_verified_at' => 'datetime',
        'analis_verified_at' => 'datetime',
    ];

    /**
     * Properti virtual untuk menyimpan catatan workflow sementara sebelum disimpan.
     */
    public ?string $workflow_notes = null;

    // --- ACTIVITY LOG CONFIGURATION ---
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'application_number',
                'status',
                'assigned_to',
                'amount_requested',
                'customer_id',
                'product_type_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Permohonan dengan nomor '{$this->application_number}' telah {$eventName}")
            ->useLogName('LoanApplication');
    }

    // --- RELATIONSHIPS ---
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function productType(): BelongsTo { return $this->belongsTo(ProductType::class, 'product_type_id'); }
    public function inputRegion(): BelongsTo { return $this->belongsTo(Region::class, 'input_region_id'); }
    public function processingRegion(): BelongsTo { return $this->belongsTo(Region::class, 'processing_region_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function documents(): HasMany { return $this->hasMany(ApplicationDocument::class); }
    public function workflows(): HasMany { return $this->hasMany(ApplicationWorkflow::class); }

    // --- MODEL EVENTS ---
    protected static function booted()
    {
        // Event ini berjalan SEBELUM record pertama kali disimpan ke database.
        static::creating(function ($application) {
            $user = Auth::user();

            // 1. Set data default jika kosong
            if ($user) {
                $application->created_by = $application->created_by ?? $user->id;
                $application->input_region_id = $application->input_region_id ?? $user->region_id;
            }

            // 2. Tentukan Unit Pemroses (`processing_region_id`)
            if (empty($application->processing_region_id) && $application->input_region_id) {
                $inputRegion = Region::find($application->input_region_id);
                if ($inputRegion?->type === 'UNIT') {
                    $application->processing_region_id = $inputRegion->id;
                } elseif ($inputRegion?->type === 'SUBUNIT' && $inputRegion->parent_id) {
                    $parentUnit = Region::find($inputRegion->parent_id);
                    if ($parentUnit && $parentUnit->type === 'UNIT') {
                        $application->processing_region_id = $parentUnit->id;
                    }
                }
            }

            // 3. Generate Nomor Permohonan
            if (empty($application->application_number)) {
                $yearMonth = date('Y/m');
                $prefix = 'APP/' . $yearMonth . '/';
                $lastApp = self::where('application_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
                $nextNumber = 1;
                if ($lastApp) {
                    $parts = explode('/', $lastApp->application_number);
                    if (is_numeric(end($parts))) { $nextNumber = intval(end($parts)) + 1; }
                }
                $application->application_number = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }

            // 4. Lakukan Penugasan Otomatis Awal jika status SUBMITTED
            if ($application->status === 'SUBMITTED' && is_null($application->assigned_to)) {
                self::setInitialAssignee($application);
            }
        });

        // Event ini berjalan SETELAH record berhasil disimpan (baik create maupun update).
        static::saved(function ($application) {
            // A. Catat ke Workflow Log
            if ($application->wasRecentlyCreated) {
                // Saat CREATE
                ApplicationWorkflow::create([
                    'loan_application_id' => $application->id,
                    'from_status' => null,
                    'to_status' => $application->status,
                    'processed_by' => $application->created_by,
                    'notes' => $application->workflow_notes ?? 'Permohonan dibuat dengan status awal ' . $application->status . '.',
                ]);
            } elseif ($application->wasChanged('status')) {
                // Saat UPDATE dan status berubah
                ApplicationWorkflow::create([
                    'loan_application_id' => $application->id,
                    'from_status' => $application->getOriginal('status'),
                    'to_status' => $application->status,
                    'processed_by' => Auth::id() ?? $application->getOriginal('assigned_to'),
                    'notes' => $application->workflow_notes ?? 'Status permohonan diubah.',
                ]);
            }
            $application->workflow_notes = null; // Selalu reset catatan virtual

            // B. Kirim Notifikasi jika penugasan berubah
            if ($application->wasChanged('assigned_to') && !is_null($application->assigned_to)) {
                $newAssignee = User::find($application->assigned_to);
                if ($newAssignee) {
                     $assigner = Auth::user() ?? User::find($application->getOriginal('assigned_to')) ?? $application->creator;
                     $newAssignee->notify(new ApplicationAssignedNotification($application, $assigner));
                }
            }
        });
    }
    
    // Helper method untuk penugasan awal
    protected static function setInitialAssignee(LoanApplication $application): void
    {
        if ($application->processing_region_id) {
            $processingUnit = Region::find($application->processing_region_id);
            if ($processingUnit && $processingUnit->type === 'UNIT') {
                $adminUnitUser = User::where('region_id', $processingUnit->id)
                                     ->whereHas('roles', fn ($query) => $query->where('name', 'Admin Unit'))->first();
                if ($adminUnitUser) {
                    $application->assigned_to = $adminUnitUser->id;
                } else {
                    $kepalaUnitUser = User::where('region_id', $processingUnit->id)
                                         ->whereHas('roles', fn ($query) => $query->where('name', 'Kepala Unit'))->first();
                    if ($kepalaUnitUser) $application->assigned_to = $kepalaUnitUser->id;
                }
            }
        }
    }
    
    // Method untuk mengisi catatan workflow sementara
    public function setWorkflowNotes(?string $notes): self
    {
        $this->workflow_notes = $notes;
        return $this;
    }
}