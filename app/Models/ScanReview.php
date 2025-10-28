<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanReview extends Model
{
    use HasFactory;

    protected $table = 'scan_reviews';

    protected $fillable = [
        'scan_id',
        'reviewer_id',
        'status',
        'comments',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function scan(): BelongsTo
    {
        return $this->belongsTo(LetterheadScan::class, 'scan_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
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

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Check if review is pending
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Check if review is approved
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // Check if review is rejected
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
