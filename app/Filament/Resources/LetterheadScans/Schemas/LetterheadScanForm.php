<?php

namespace App\Filament\Resources\LetterheadScans\Schemas;

use Filament\Schemas\Schema;

class LetterheadScanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('print_result_id')
                    ->label('Print Result')
                    ->relationship('printResult', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        "Request #{$record->printRequest->request_number} - {$record->template->name}"
                    )
                    ->required()
                    ->searchable(),
                
                \Filament\Forms\Components\Select::make('scan_type')
                    ->options([
                        'successful' => 'Successful Print',
                        'wasted' => 'Wasted Print',
                    ])
                    ->required(),
                
                \Filament\Forms\Components\FileUpload::make('file_path')
                    ->label('Scan File')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(10240) // 10MB
                    ->directory('letterhead-scans')
                    ->required(),
                
                \Filament\Forms\Components\TextInput::make('file_name')
                    ->label('File Name')
                    ->required()
                    ->maxLength(255),
                
                \Filament\Forms\Components\TextInput::make('mime_type')
                    ->label('MIME Type')
                    ->required()
                    ->maxLength(100),
                
                \Filament\Forms\Components\TextInput::make('file_size')
                    ->label('File Size (bytes)')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                
                \Filament\Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\Select::make('uploaded_by')
                    ->label('Uploaded By')
                    ->relationship('uploader', 'name')
                    ->default(auth()->id())
                    ->required(),
            ]);
    }
}
