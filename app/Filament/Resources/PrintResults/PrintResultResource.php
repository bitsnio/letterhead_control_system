<?php

namespace App\Filament\Resources\PrintResults;

use App\Filament\Resources\PrintResults\Pages\CreatePrintResult;
use App\Filament\Resources\PrintResults\Pages\EditPrintResult;
use App\Filament\Resources\PrintResults\Pages\ListPrintResults;
use App\Filament\Resources\PrintResults\Schemas\PrintResultForm;
use App\Filament\Resources\PrintResults\Tables\PrintResultsTable;
use App\Models\PrintResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrintResultResource extends Resource
{
    protected static ?string $model = PrintResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PrintResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrintResultsTable::configure($table);
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
            'index' => ListPrintResults::route('/'),
            'create' => CreatePrintResult::route('/create'),
            'edit' => EditPrintResult::route('/{record}/edit'),
        ];
    }
}
