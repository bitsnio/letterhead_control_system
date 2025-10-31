<?php

namespace App\Filament\Resources\LetterheadTemplates\Pages;

use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;


class EditLetterheadTemplate extends EditRecord
{
    protected static string $resource = LetterheadTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->approval_status !== 'approved'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Template updated')
            ->body('The template has been updated successfully.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Reset approval status if content is changed
        if ($this->record->approval_status === 'approved' && 
            $this->record->content !== $data['content']) {
            $data['approval_status'] = 'pending';
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
        
        return $data;
    }
}
