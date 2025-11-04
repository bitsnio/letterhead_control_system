<?php

namespace App\Filament\Resources\ScanReviews\Pages;

use App\Filament\Resources\ScanReviews\ScanReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScanReviews extends ListRecords
{
    protected static string $resource = ScanReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
