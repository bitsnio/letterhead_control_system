<?php

namespace App\Filament\Resources\TemplateApprovals;

use App\Filament\Resources\TemplateApprovals\Pages\CreateTemplateApproval;
use App\Filament\Resources\TemplateApprovals\Pages\EditTemplateApproval;
use App\Filament\Resources\TemplateApprovals\Pages\ListTemplateApprovals;
use App\Filament\Resources\TemplateApprovals\Schemas\TemplateApprovalForm;
use App\Filament\Resources\TemplateApprovals\Tables\TemplateApprovalsTable;
use App\Models\TemplateApproval;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TemplateApprovalResource extends Resource
{
    protected static ?string $model = TemplateApproval::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TemplateApprovalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TemplateApprovalsTable::configure($table);
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
            'index' => ListTemplateApprovals::route('/'),
            // 'create' => CreateTemplateApproval::route('/create'),
            'edit' => EditTemplateApproval::route('/{record}/edit'),
        ];
    }
}
