<?php

namespace App\Filament\Resources\ScanReviews\Pages;

use App\Filament\Resources\ScanReviews\ScanReviewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScanReview extends EditRecord
{
    protected static string $resource = ScanReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
