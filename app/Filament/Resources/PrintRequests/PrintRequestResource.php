<?php

namespace App\Filament\Resources\PrintRequests;

use App\Filament\Resources\PrintRequests\Pages\CreatePrintRequest;
use App\Filament\Resources\PrintRequests\Pages\EditPrintRequest;
use App\Filament\Resources\PrintRequests\Pages\ListPrintRequests;
use App\Filament\Resources\PrintRequests\Schemas\PrintRequestForm;
use App\Filament\Resources\PrintRequests\Tables\PrintRequestsTable;
use App\Models\PrintRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrintRequestResource extends Resource
{
    protected static ?string $model = PrintRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PrintRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrintRequestsTable::configure($table);
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
            'index' => ListPrintRequests::route('/'),
            'create' => CreatePrintRequest::route('/create'),
            'edit' => EditPrintRequest::route('/{record}/edit'),
        ];
    }
}
