<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\LetterheadTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class TemplateAwaitingApproval extends Notification
{
    use Queueable;

    public function __construct(
        public LetterheadTemplate $template
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Template Awaiting Approval')
            ->body("Template '{$this->template->name}' is waiting for your approval.")
            ->icon('heroicon-o-clock')
            ->iconColor('warning')
            ->actions([
                Action::make('review')
                    ->label('Review')
                    ->url(route('filament.admin.resources.print-templates.approve', ['record' => $this->template]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
