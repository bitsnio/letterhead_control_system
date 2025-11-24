<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'letterhead_inventory_id',
        'print_job_id',
        'serial_number',
        'letterhead_template_id',
        'used_at',
        'scanned_copy', // Add this field
        'notes', // Add this field
    ];

    protected $casts = [
        'serial_number' => 'integer',
        'used_at' => 'datetime',
    ];

    public function letterhead(): BelongsTo
    {
        return $this->belongsTo(LetterheadInventory::class);
    }

    public function printJob(): BelongsTo
    {
        return $this->belongsTo(PrintJob::class);
    }

     public function template(): BelongsTo
    {
        return $this->belongsTo(LetterheadTemplate::class, 'letterhead_template_id');
    }


    public function canEditSerial(): bool
    {
        return $this->printJob &&
            $this->printJob->status === 'completed' &&
            empty($this->scanned_copy);
    }

    /**
     * Check if scanned copy exists
     */
    public function getHasScannedCopyAttribute(): bool
    {
        return !empty($this->scanned_copy);
    }

    /**
     * Scope for serials within a specific range
     */
    public function scopeInRange($query, $startSerial, $endSerial)
    {
        return $query->whereBetween('serial_number', [$startSerial, $endSerial]);
    }

    /**
     * Scope for serials in a specific print job
     */
    public function scopeForPrintJob($query, $printJobId)
    {
        return $query->where('print_job_id', $printJobId);
    }
}
