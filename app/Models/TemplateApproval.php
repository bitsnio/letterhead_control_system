<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateApproval extends Model
{
    use HasFactory;

    protected $table = 'template_approvals';

    protected $fillable = [
        'template_id',
        'approver_id',
        'status',
        'comments',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterheadTemplate::class, 'template_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
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

    // Check if approval is pending
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Check if approval is approved
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // Check if approval is rejected
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
