<?php

namespace App\Filament\Resources\LetterheadInventories\Pages;

use App\Filament\Resources\LetterheadInventories\LetterheadInventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLetterheadInventory extends EditRecord
{
    protected static string $resource = LetterheadInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
