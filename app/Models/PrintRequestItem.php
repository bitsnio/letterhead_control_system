<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintRequestItem extends Model
{
    use HasFactory;

    protected $table = 'print_request_items';

    protected $fillable = [
        'print_request_id',
        'template_id',
        'quantity',
        'start_serial',
        'end_serial',
        'notes',
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

    // Get serial range as string
    public function getSerialRangeAttribute(): string
    {
        if ($this->start_serial && $this->end_serial) {
            return "{$this->start_serial} - {$this->end_serial}";
        } elseif ($this->start_serial) {
            return "From {$this->start_serial}";
        } elseif ($this->end_serial) {
            return "To {$this->end_serial}";
        }
        return 'No serial range';
    }
}
