<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\LetterheadInventory;
use App\Models\LetterheadTemplate;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'templates',
        'variable_data',
        'quantity',
        'start_serial',
        'end_serial',
        'letterhead_id',
        'status',
        'printed_at',
    ];

    protected $casts = [
        'templates' => 'array',
        'variable_data' => 'array',
        'quantity' => 'integer',
        'start_serial' => 'integer',
        'end_serial' => 'integer',
        'printed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function letterhead(): BelongsTo
    {
        return $this->belongsTo(LetterheadInventory::class);
    }

    public function serialUsages(): HasMany
    {
        return $this->hasMany(SerialUsage::class);
    }

    public function getTemplateRecords()
    {
        return LetterheadTemplate::whereIn('id', $this->templates)->get();
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'printed_at' => now(),
        ]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    public function getSerialUsageStats(): array
    {
        $total = $this->quantity;
        $withScans = $this->serialUsages()->whereNotNull('scanned_copy')->count();
        $withoutScans = $total - $withScans;

        return [
            'total' => $total,
            'with_scans' => $withScans,
            'without_scans' => $withoutScans,
            'completion_percentage' => $total > 0 ? round(($withScans / $total) * 100, 2) : 0,
        ];
    }

    public function canEditSerials(): bool
    {
        return $this->status === 'completed';
    }
}
