<?php

namespace App\Filament\Resources\PrintResults\Pages;

use App\Filament\Resources\PrintResults\PrintResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrintResult extends EditRecord
{
    protected static string $resource = PrintResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
