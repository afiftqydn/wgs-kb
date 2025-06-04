<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationWorkflow extends Model
{
    use HasFactory;

    // Karena kita hanya menggunakan created_at dan tidak ada updated_at di migrasi,
    // kita set public static $timestamps = false; jika kita tidak ingin updated_at sama sekali
    // atau pastikan migrasi hanya punya $table->timestamp('created_at')->useCurrent();
    // Jika Anda menggunakan $table->timestamps() di migrasi (yang membuat created_at & updated_at),
    // maka biarkan default (atau set $timestamps = true).
    // Untuk log, biasanya updated_at tidak begitu relevan.
    // Jika migrasi hanya ada created_at:
    const CREATED_AT = 'created_at'; // Memberitahu Eloquent nama kolom created_at
    const UPDATED_AT = null; // Memberitahu Eloquent tidak ada kolom updated_at

    protected $fillable = [
        'loan_application_id',
        'from_status',
        'to_status',
        'processed_by',
        'notes',
        // 'created_at' akan diisi otomatis jika menggunakan ->useCurrent() di migrasi atau default Eloquent
    ];

    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function processor(): BelongsTo // User yang memproses
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
