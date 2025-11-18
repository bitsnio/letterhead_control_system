<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'letterhead_id',
        'print_job_id',
        'serial_number',
        'used_at',
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
}