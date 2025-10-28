<?php

namespace App\Filament\Resources\LetterheadInventories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class LetterheadInventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('current_quantity')
                    ->numeric()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('minimum_level')
                    ->numeric()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('unit')
                    ->searchable(),
                
                \Filament\Tables\Columns\TextColumn::make('cost_per_unit')
                    ->money('USD')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('supplier')
                    ->searchable(),
                
                \Filament\Tables\Columns\TextColumn::make('last_restocked')
                    ->date()
                    ->sortable(),
                
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                
                \Filament\Tables\Columns\TextColumn::make('stock_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'out_of_stock' => 'danger',
                        'low_stock' => 'warning',
                        'in_stock' => 'success',
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active'),
                \Filament\Tables\Filters\SelectFilter::make('stock_status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'low_stock' => 'Low Stock',
                        'out_of_stock' => 'Out of Stock',
                    ]),
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
