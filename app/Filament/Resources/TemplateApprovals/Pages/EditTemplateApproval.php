<?php

namespace App\Filament\Resources\TemplateApprovals\Pages;

use App\Filament\Resources\TemplateApprovals\TemplateApprovalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTemplateApproval extends EditRecord
{
    protected static string $resource = TemplateApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
