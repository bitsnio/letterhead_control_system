<?php

namespace App\Filament\Resources\PrintResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class PrintResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('printRequest.request_number')
                    ->label('Request #')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('requested_quantity')
                    ->label('Requested')
                    ->numeric()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('successful_prints')
                    ->label('Successful')
                    ->numeric()
                    ->sortable()
                    ->color('success'),
                
                \Filament\Tables\Columns\TextColumn::make('wasted_prints')
                    ->label('Wasted')
                    ->numeric()
                    ->sortable()
                    ->color('danger'),
                
                \Filament\Tables\Columns\TextColumn::make('total_prints')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('success_rate')
                    ->label('Success Rate')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 70 ? 'warning' : 'danger')),
                
                \Filament\Tables\Columns\TextColumn::make('wastage_percentage')
                    ->label('Wastage %')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->color(fn ($state) => $state <= 5 ? 'success' : ($state <= 15 ? 'warning' : 'danger')),
                
                \Filament\Tables\Columns\TextColumn::make('printer.name')
                    ->label('Printed By')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('printed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('print_request_id')
                    ->relationship('printRequest', 'request_number')
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('template_id')
                    ->relationship('template', 'name')
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('printed_by')
                    ->relationship('printer', 'name')
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
