<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintRequest extends Model
{
    use HasFactory;

    protected $table = 'print_requests';

    protected $fillable = [
        'request_number',
        'requested_by',
        'status',
        'notes',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrintRequestItem::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(PrintResult::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePrinting($query)
    {
        return $query->where('status', 'printing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Check if request is pending
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Check if request is approved
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // Check if request is printing
    public function isPrinting(): bool
    {
        return $this->status === 'printing';
    }

    // Check if request is completed
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // Get total quantity of all items
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}
