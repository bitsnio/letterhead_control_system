<?php

namespace App\Filament\Resources\ScanReviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ScanReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('scan.file_name')
                    ->label('Scan File')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('scan.scan_type')
                    ->label('Scan Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'successful' => 'success',
                        'wasted' => 'danger',
                    }),
                
                \Filament\Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                
                \Filament\Tables\Columns\TextColumn::make('comments')
                    ->limit(50)
                    ->tooltip(function (\Filament\Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                
                \Filament\Tables\Columns\TextColumn::make('reviewed_at')
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
                        'rejected' => 'Rejected',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('scan_id')
                    ->relationship('scan', 'file_name')
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('reviewer_id')
                    ->relationship('reviewer', 'name')
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
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comments')
                            ->label('Approval Comments')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'comments' => $data['comments'] ?? null,
                            'reviewed_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Scan approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comments')
                            ->label('Rejection Comments')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'comments' => $data['comments'],
                            'reviewed_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Scan rejected')
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
