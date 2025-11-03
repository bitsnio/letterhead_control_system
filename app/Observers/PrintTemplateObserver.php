<?php

namespace App\Observers;

use App\Models\LetterheadTemplate;
use App\Notifications\TemplateAwaitingApproval;




class PrintTemplateObserver
{
    public function created(LetterheadTemplate $template): void
    {
        // Notify the first approver
        $this->notifyNextApprover($template);
    }

    public function updated(LetterheadTemplate $template): void
    {
        // If approval status changed, handle notifications
        if ($template->wasChanged('approval_status')) {
            if ($template->approval_status === 'pending') {
                $this->notifyNextApprover($template);
            }
        }
    }

    protected function notifyNextApprover(LetterheadTemplate $template): void
    {
        $nextApproval = $template->getCurrentPendingApproval();
        
        if ($nextApproval && $nextApproval->approver) {
            $nextApproval->approver->notify(
                new TemplateAwaitingApproval($template)
            );
        }
    }
}
