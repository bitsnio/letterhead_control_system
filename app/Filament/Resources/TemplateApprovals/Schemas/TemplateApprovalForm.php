<?php

namespace App\Filament\Resources\TemplateApprovals\Schemas;

use Filament\Schemas\Schema;

class TemplateApprovalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('template_id')
                    ->label('Template')
                    ->relationship('template', 'name')
                    ->required()
                    ->searchable(),
                
                \Filament\Forms\Components\Select::make('approver_id')
                    ->label('Approver')
                    ->relationship('approver', 'name')
                    ->required()
                    ->searchable(),
                
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
                
                \Filament\Forms\Components\Textarea::make('comments')
                    ->label('Comments')
                    ->rows(4)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\DateTimePicker::make('reviewed_at')
                    ->label('Reviewed At'),
            ]);
    }
}
