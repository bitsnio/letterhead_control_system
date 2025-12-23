<?php

namespace App\Filament\Resources\LetterheadInventories;

use App\Filament\Resources\LetterheadInventories\Pages\CreateLetterheadInventory;
use App\Filament\Resources\LetterheadInventories\Pages\EditLetterheadInventory;
use App\Filament\Resources\LetterheadInventories\Pages\ListLetterheadInventories;
use App\Filament\Resources\LetterheadInventories\Schemas\LetterheadInventoryForm;
use App\Filament\Resources\LetterheadInventories\Tables\LetterheadInventoriesTable;
use App\Models\LetterheadInventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Traits\HasNavigationPermission;

class LetterheadInventoryResource extends Resource
{
    use HasNavigationPermission;
    
    protected static ?string $model = LetterheadInventory::class;

    protected static ?string $navigationLabel = 'Recieve Letterheads';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LetterheadInventoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LetterheadInventoriesTable::configure($table);
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
            'index' => ListLetterheadInventories::route('/'),
            'create' => CreateLetterheadInventory::route('/create'),
            'edit' => EditLetterheadInventory::route('/{record}/edit'),
        ];
    }
}
