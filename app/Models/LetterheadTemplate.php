<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterheadTemplate extends Model
{
    use HasFactory;

    protected $table = 'letterhead_templates';

    protected $fillable = [
        'name',
        'description',
        'content',
        'template_file',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'is_active',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TemplateApproval::class, 'template_id');
    }

    public function printRequestItems(): HasMany
    {
        return $this->hasMany(PrintRequestItem::class, 'template_id');
    }

    public function printResults(): HasMany
    {
        return $this->hasMany(PrintResult::class, 'template_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Check if template is approved
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // Check if template is pending approval
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }
}
