<?php

namespace App\Filament\Resources\PrintedLetterheads\Pages;

use App\Filament\Resources\PrintedLetterheads\PrintedLetterheadsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrintedLetterheads extends EditRecord
{

    protected static string $resource = PrintedLetterheadsResource::class;


    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
 
       ];
    }
}
