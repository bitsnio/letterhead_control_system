<?php

namespace App\Filament\Resources\PrintRequests\Pages;

use App\Filament\Resources\PrintRequests\PrintRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrintRequests extends ListRecords
{
    protected static string $resource = PrintRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
