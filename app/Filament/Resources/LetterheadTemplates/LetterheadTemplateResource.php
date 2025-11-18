<?php

namespace App\Filament\Resources\LetterheadTemplates;

use App\Filament\Resources\LetterheadTemplates\Pages\CreateLetterheadTemplate;
use App\Filament\Resources\LetterheadTemplates\Pages\EditLetterheadTemplate;
use App\Filament\Resources\LetterheadTemplates\Schemas\LetterheadTemplateForm;
use App\Filament\Resources\LetterheadTemplates\Pages\ListLetterheadTemplates;
use App\Filament\Resources\LetterheadTemplates\Pages\ViewLetterheadTemplate;
use App\Filament\Resources\LetterheadTemplates\Pages\PrintLetterheadTemplate;
use App\Filament\Resources\LetterheadTemplates\Tables\LetterheadTemplatesTable;
use App\Filament\Resources\TemplateApprovals\Pages\ListTemplateApprovals;
use App\Models\LetterheadTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\LetterheadTemplates\Pages\ApproveLetterheadTemplate;

class LetterheadTemplateResource extends Resource
{
    protected static ?string $model = LetterheadTemplate::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Print Templates';

    // protected static string|UnitEnum|null $navigationGroup = 'Templates';

    public static function form(Schema $schema): Schema
    {
        return LetterheadTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LetterheadTemplatesTable::configure($table);
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
            'index' => ListLetterheadTemplates::route('/'),
            'create' => CreateLetterheadTemplate::route('/create'),
            'edit' => EditLetterheadTemplate::route('/{record}/edit'),
            'view' => ViewLetterheadTemplate::route('/{record}'),
            'approve' => ApproveLetterheadTemplate::route('/{record}/approve'),
            'print' => PrintLetterheadTemplate::route('/{record}/print'),
        ];
    }

   
}
