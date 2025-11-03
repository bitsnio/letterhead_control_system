<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\LetterheadTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class TemplateApproved extends Notification
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
            ->title('Template Approved')
            ->body("Your template '{$this->template->name}' has been approved and is now ready to use.")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->actions([
                Action::make('view')
                    ->label('View Template')
                    ->url(route('filament.admin.resources.print-templates.view', ['record' => $this->template]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
