<?php

namespace App\Filament\Resources\LetterheadTemplates\Pages;

use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLetterheadTemplates extends ListRecords
{
    protected static string $resource = LetterheadTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
