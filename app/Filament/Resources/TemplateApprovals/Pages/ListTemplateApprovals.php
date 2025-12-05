<?php

namespace App\Filament\Resources\TemplateApprovals\Pages;

use App\Filament\Resources\TemplateApprovals\TemplateApprovalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTemplateApprovals extends ListRecords
{
    protected static string $resource = TemplateApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
