<?php

namespace App\Models;

use App\Notifications\TemplateApproved;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;



class LetterheadTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'content',
        'variables',
        'approval_status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            $template->created_by = auth()->id();
            $template->approval_status = 'pending';
        });

        static::saving(function ($template) {
            // Extract variables from content
            if ($template->content) {
                preg_match_all('/\$([a-zA-Z0-9_]+)\$/', $template->content, $matches);
                $template->variables = array_unique($matches[1]);
            }
        });

        static::created(function ($template) {
            // Create approval hierarchy
            $template->createApprovalHierarchy();
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TemplateApproval::class, 'template_id');
    }

    public function createApprovalHierarchy()
    {
        // Define your approval hierarchy here
        // You can customize this based on your needs
        $approvers = $this->getApprovalHierarchy();

        foreach ($approvers as $level => $approverId) {
            $this->approvals()->create([
                'approver_id' => $approverId,
                'level' => $level,
                'status' => 'pending',
            ]);
        }
    }

    protected function getApprovalHierarchy(): array
    {
        // Example: Get approvers based on business logic
        // You can customize this method based on your requirements
        // Return array with level => user_id

        // Example hierarchy:
        // Level 1: Manager
        // Level 2: Director

        return [
            1 => $this->getManagerId(),
            2 => $this->getDirectorId(),
        ];
    }

    protected function getManagerId()
    {
        // Add logic to get manager ID
        // This is a placeholder - customize based on your needs
        return User::where('role', 'manager')->first()?->id ?? 1;
    }

    protected function getDirectorId()
    {
        // Add logic to get director ID
        // This is a placeholder - customize based on your needs
        return User::where('role', 'director')->first()?->id ?? 1;
    }

    public function getCurrentPendingApproval(): ?TemplateApproval
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->orderBy('level')
            ->first();
    }

    public function canBeApprovedBy(User $user): bool
    {
        $currentApproval = $this->getCurrentPendingApproval();

        return $currentApproval && $currentApproval->approver_id === $user->id;
    }

    public function approve(User $user, ?string $comments = null)
    {
        $approval = $this->approvals()
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return false;
        }

        $approval->update([
            'status' => 'approved',
            'comments' => $comments,
            'actioned_at' => now(),
        ]);

        // Check if all approvals are complete
        $pendingApprovals = $this->approvals()->where('status', 'pending')->count();

        if ($pendingApprovals === 0) {
            $this->update([
                'approval_status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
            // Notify the creator
            if ($this->createdBy) {
                $this->createdBy->notify(
                    new \App\Notifications\TemplateApproved($this)
                );
            }
        } else {
            // Notify next approver
            $nextApproval = $this->getCurrentPendingApproval();
            if ($nextApproval && $nextApproval->approver) {
                $nextApproval->approver->notify(
                    new \App\Notifications\TemplateAwaitingApproval($this)
                );
            }
        }

        return true;
    }

    public function reject(User $user, string $reason)
    {
        $approval = $this->approvals()
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return false;
        }

        $approval->update([
            'status' => 'rejected',
            'comments' => $reason,
            'actioned_at' => now(),
        ]);

        $this->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        // Notify the creator
        if ($this->createdBy) {
            $this->createdBy->notify(
                new \App\Notifications\TemplateRejected($this, $reason)
            );
        }

        return true;
    }
}
