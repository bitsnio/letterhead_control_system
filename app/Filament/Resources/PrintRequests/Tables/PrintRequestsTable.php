<?php

namespace App\Filament\Resources\PrintRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PrintRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('request_number')
                    ->label('Request #')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'printing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                
                \Filament\Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                
                \Filament\Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total Qty')
                    ->getStateUsing(fn ($record) => $record->items->sum('quantity')),
                
                \Filament\Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'printing' => 'Printing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('requested_by')
                    ->relationship('requester', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
                
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Print request approved')
                            ->success()
                            ->send();
                    }),
                
                Action::make('start_printing')
                    ->label('Start Printing')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'approved')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'printing']);
                        
                        Notification::make()
                            ->title('Printing started')
                            ->info()
                            ->send();
                    }),
                
                Action::make('complete')
                    ->label('Mark Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'printing')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Print request completed')
                            ->success()
                            ->send();
                    }),
                
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'approved', 'printing']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'cancelled']);
                        
                        Notification::make()
                            ->title('Print request cancelled')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
