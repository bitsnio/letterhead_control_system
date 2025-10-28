<?php

namespace App\Filament\Resources\UserAssignments\Pages;

use App\Filament\Resources\UserAssignments\UserAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserAssignment extends EditRecord
{
    protected static string $resource = UserAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
