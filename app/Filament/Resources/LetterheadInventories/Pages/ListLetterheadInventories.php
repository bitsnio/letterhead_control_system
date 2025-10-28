<?php

namespace App\Filament\Resources\LetterheadInventories\Pages;

use App\Filament\Resources\LetterheadInventories\LetterheadInventoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLetterheadInventories extends ListRecords
{
    protected static string $resource = LetterheadInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
