<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintResult extends Model
{
    use HasFactory;

    protected $table = 'print_results';

    protected $fillable = [
        'print_request_id',
        'template_id',
        'requested_quantity',
        'successful_prints',
        'wasted_prints',
        'wastage_reason',
        'printed_by',
        'printed_at',
        'status',
        'assigned_to',
        'status_notes',
        'status_updated_at',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    // Relationships
    public function printRequest(): BelongsTo
    {
        return $this->belongsTo(PrintRequest::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterheadTemplate::class, 'template_id');
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(LetterheadScan::class);
    }

    // Get total prints (successful + wasted)
    public function getTotalPrintsAttribute(): int
    {
        return $this->successful_prints + $this->wasted_prints;
    }

    // Get wastage percentage
    public function getWastagePercentageAttribute(): float
    {
        if ($this->total_prints === 0) {
            return 0;
        }
        return round(($this->wasted_prints / $this->total_prints) * 100, 2);
    }

    // Get success rate
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_prints === 0) {
            return 0;
        }
        return round(($this->successful_prints / $this->total_prints) * 100, 2);
    }
}
