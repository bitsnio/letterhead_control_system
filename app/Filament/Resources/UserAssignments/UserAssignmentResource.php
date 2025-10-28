<?php

namespace App\Filament\Resources\UserAssignments;

use App\Filament\Resources\UserAssignments\Pages\CreateUserAssignment;
use App\Filament\Resources\UserAssignments\Pages\EditUserAssignment;
use App\Filament\Resources\UserAssignments\Pages\ListUserAssignments;
use App\Filament\Resources\UserAssignments\Schemas\UserAssignmentForm;
use App\Filament\Resources\UserAssignments\Tables\UserAssignmentsTable;
use App\Models\UserAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserAssignmentResource extends Resource
{
    protected static ?string $model = UserAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAssignmentsTable::configure($table);
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
            'index' => ListUserAssignments::route('/'),
            'create' => CreateUserAssignment::route('/create'),
            'edit' => EditUserAssignment::route('/{record}/edit'),
        ];
    }
}
