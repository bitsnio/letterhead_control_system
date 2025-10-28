<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assignment_type',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTemplateApprovers($query)
    {
        return $query->where('assignment_type', 'template_approver');
    }

    public function scopePrintApprovers($query)
    {
        return $query->where('assignment_type', 'print_approver');
    }

    public function scopeScanReviewers($query)
    {
        return $query->where('assignment_type', 'scan_reviewer');
    }

    // Static methods for getting assignees
    public static function getTemplateApprovers()
    {
        return static::active()
            ->templateApprovers()
            ->with('user')
            ->orderBy('priority')
            ->get();
    }

    public static function getPrintApprovers()
    {
        return static::active()
            ->printApprovers()
            ->with('user')
            ->orderBy('priority')
            ->get();
    }

    public static function getScanReviewers()
    {
        return static::active()
            ->scanReviewers()
            ->with('user')
            ->orderBy('priority')
            ->get();
    }
}
