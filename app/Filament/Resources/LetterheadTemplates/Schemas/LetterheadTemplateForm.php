<?php

namespace App\Filament\Resources\LetterheadTemplates\Schemas;

use Filament\Schemas\Schema;

class LetterheadTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                \Filament\Forms\Components\Textarea::make('description')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\FileUpload::make('template_file')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240), // 10MB
                
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('draft')
                    ->required(),
                
                \Filament\Forms\Components\Select::make('created_by')
                    ->relationship('creator', 'name')
                    ->default(auth()->id())
                    ->required(),
                
                \Filament\Forms\Components\Select::make('approved_by')
                    ->relationship('approver', 'name'),
                
                \Filament\Forms\Components\DateTimePicker::make('approved_at'),
                
                \Filament\Forms\Components\Textarea::make('rejection_reason')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
