<?php

namespace App\Filament\Resources\LetterheadTemplates\Pages;

use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateLetterheadTemplate extends CreateRecord
{
    protected static string $resource = LetterheadTemplateResource::class;
    // protected static string $resource = PrintTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Template created')
            ->body('The template has been created and submitted for approval.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['approval_status'] = 'pending';
        
        return $data;
    }
}
