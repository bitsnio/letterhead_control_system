<?php

namespace App\Filament\Resources\PrintRequests\Pages;

use App\Filament\Resources\PrintRequests\PrintRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrintRequest extends EditRecord
{
    protected static string $resource = PrintRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
