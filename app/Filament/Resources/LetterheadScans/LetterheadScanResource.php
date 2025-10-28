<?php

namespace App\Filament\Resources\LetterheadScans;

use App\Filament\Resources\LetterheadScans\Pages\CreateLetterheadScan;
use App\Filament\Resources\LetterheadScans\Pages\EditLetterheadScan;
use App\Filament\Resources\LetterheadScans\Pages\ListLetterheadScans;
use App\Filament\Resources\LetterheadScans\Schemas\LetterheadScanForm;
use App\Filament\Resources\LetterheadScans\Tables\LetterheadScansTable;
use App\Models\LetterheadScan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LetterheadScanResource extends Resource
{
    protected static ?string $model = LetterheadScan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LetterheadScanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LetterheadScansTable::configure($table);
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
            'index' => ListLetterheadScans::route('/'),
            'create' => CreateLetterheadScan::route('/create'),
            'edit' => EditLetterheadScan::route('/{record}/edit'),
        ];
    }
}
