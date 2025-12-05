<?php

namespace App\Filament\Resources\PrintedLetterheads\Pages;

use App\Filament\Resources\PrintedLetterheads\PrintedLetterheadsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrintedLetterheads extends ListRecords
{
    protected static string $resource = PrintedLetterheadsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
