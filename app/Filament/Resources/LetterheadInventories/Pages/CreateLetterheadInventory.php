<?php

namespace App\Filament\Resources\LetterheadInventories\Pages;

use App\Filament\Resources\LetterheadInventories\LetterheadInventoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLetterheadInventory extends CreateRecord
{
    protected static string $resource = LetterheadInventoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
