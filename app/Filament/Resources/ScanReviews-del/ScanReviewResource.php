<?php

namespace App\Filament\Resources\ScanReviews;

use App\Filament\Resources\ScanReviews\Pages\CreateScanReview;
use App\Filament\Resources\ScanReviews\Pages\EditScanReview;
use App\Filament\Resources\ScanReviews\Pages\ListScanReviews;
use App\Filament\Resources\ScanReviews\Schemas\ScanReviewForm;
use App\Filament\Resources\ScanReviews\Tables\ScanReviewsTable;
use App\Models\ScanReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScanReviewResource extends Resource
{
    protected static ?string $model = ScanReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ScanReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScanReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScanReviews::route('/'),
            'create' => CreateScanReview::route('/create'),
            'edit' => EditScanReview::route('/{record}/edit'),
        ];
    }
}
