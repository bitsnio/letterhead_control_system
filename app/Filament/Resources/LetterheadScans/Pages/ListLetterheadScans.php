<?php

namespace App\Filament\Resources\LetterheadScans\Pages;

use App\Filament\Resources\LetterheadScans\LetterheadScanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLetterheadScans extends ListRecords
{
    protected static string $resource = LetterheadScanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
