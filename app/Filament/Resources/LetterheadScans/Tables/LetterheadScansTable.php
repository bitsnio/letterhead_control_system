<?php

namespace App\Filament\Resources\LetterheadScans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class LetterheadScansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('printResult.printRequest.request_number')
                    ->label('Request #')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('printResult.template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('scan_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'successful' => 'success',
                        'wasted' => 'danger',
                    }),
                
                \Filament\Tables\Columns\TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable()
                    ->limit(30),
                
                \Filament\Tables\Columns\TextColumn::make('file_size_human')
                    ->label('File Size')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (\Filament\Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('scan_type')
                    ->options([
                        'successful' => 'Successful',
                        'wasted' => 'Wasted',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('print_result_id')
                    ->relationship('printResult', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        "Request #{$record->printRequest->request_number} - {$record->template->name}"
                    )
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('uploaded_by')
                    ->relationship('uploader', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
