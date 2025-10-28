<?php

namespace App\Filament\Resources\UserAssignments\Pages;

use App\Filament\Resources\UserAssignments\UserAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAssignments extends ListRecords
{
    protected static string $resource = UserAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
