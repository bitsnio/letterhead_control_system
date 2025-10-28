<?php

namespace App\Filament\Resources\ScanReviews\Schemas;

use Filament\Schemas\Schema;

class ScanReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('scan_id')
                    ->label('Scan')
                    ->relationship('scan', 'file_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        "{$record->file_name} ({$record->scan_type})"
                    )
                    ->required()
                    ->searchable(),
                
                \Filament\Forms\Components\Select::make('reviewer_id')
                    ->label('Reviewer')
                    ->relationship('reviewer', 'name')
                    ->default(auth()->id())
                    ->required(),
                
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
                
                \Filament\Forms\Components\Textarea::make('comments')
                    ->label('Review Comments')
                    ->rows(4)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\DateTimePicker::make('reviewed_at')
                    ->label('Reviewed At'),
            ]);
    }
}
