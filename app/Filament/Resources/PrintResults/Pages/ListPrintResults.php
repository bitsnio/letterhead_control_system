<?php

namespace App\Filament\Resources\PrintResults\Pages;

use App\Filament\Resources\PrintResults\PrintResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrintResults extends ListRecords
{
    protected static string $resource = PrintResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
