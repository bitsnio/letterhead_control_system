<?php

namespace App\Notifications;

use App\Models\LetterheadTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Actions\Action;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;



class TemplateRejected extends Notification
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
            ->title('Template Rejected')
            ->body("Your template '{$this->template->name}' has been rejected. Reason: {$this->reason}")
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->actions([
                Action::make('view')
                    ->label('View Details')
                    ->url(route('filament.admin.resources.letterhead-templates.edit', ['record' => $this->template]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
