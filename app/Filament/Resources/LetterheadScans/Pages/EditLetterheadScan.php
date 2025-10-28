<?php

namespace App\Filament\Resources\LetterheadScans\Pages;

use App\Filament\Resources\LetterheadScans\LetterheadScanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLetterheadScan extends EditRecord
{
    protected static string $resource = LetterheadScanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
