<?php

namespace App\Filament\Resources\LetterheadTemplates\Pages;

use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLetterheadTemplate extends EditRecord
{
    protected static string $resource = LetterheadTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
