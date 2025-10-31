<?php

namespace App\Filament\Resources\LetterheadInventories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;

class LetterheadInventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('batch_name')
                    ->searchable()
                    ->sortable()
                    ->label('Batch Name'),
                
                Tables\Columns\TextColumn::make('start_serial')
                    ->sortable()
                    ->label('Start Serial'),
                
                Tables\Columns\TextColumn::make('end_serial')
                    ->sortable()
                    ->label('End Serial'),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->label('Quantity'),
                
                Tables\Columns\TextColumn::make('received_date')
                    ->date()
                    ->sortable()
                    ->label('Received Date'),
                
                Tables\Columns\TextColumn::make('supplier')
                    ->searchable()
                    ->toggleable()
                    ->label('Supplier'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created At'),
            ])
            ->filters([
                Tables\Filters\Filter::make('received_date')
                    ->schema([
                        Forms\Components\DatePicker::make('received_from'),
                        Forms\Components\DatePicker::make('received_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['received_from'], fn ($q, $date) => $q->whereDate('received_date', '>=', $date))
                            ->when($data['received_until'], fn ($q, $date) => $q->whereDate('received_date', '<=', $date));
                    }),
            ])->defaultSort('received_date', 'desc');
}

}