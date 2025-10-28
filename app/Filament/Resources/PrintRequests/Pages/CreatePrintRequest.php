<?php

namespace App\Filament\Resources\PrintRequests\Pages;

use App\Filament\Resources\PrintRequests\PrintRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrintRequest extends CreateRecord
{
    protected static string $resource = PrintRequestResource::class;
}
