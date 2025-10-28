<?php

namespace App\Filament\Resources\LetterheadTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class LetterheadTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending_approval' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                
                \Filament\Tables\Columns\TextColumn::make('creator.name')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('approver.name')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
                
                Action::make('submit_for_approval')
                    ->label('Submit for Approval')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'pending_approval']);
                        
                        // Create approval record
                        \App\Models\TemplateApproval::create([
                            'template_id' => $record->id,
                            'approver_id' => auth()->id(),
                            'status' => 'pending',
                        ]);
                        
                        Notification::make()
                            ->title('Template submitted for approval')
                            ->success()
                            ->send();
                    }),
                
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending_approval')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('comments')
                            ->label('Approval Comments')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        
                        // Update approval record
                        $approval = \App\Models\TemplateApproval::where('template_id', $record->id)
                            ->where('approver_id', auth()->id())
                            ->first();
                        
                        if ($approval) {
                            $approval->update([
                                'status' => 'approved',
                                'comments' => $data['comments'] ?? null,
                                'reviewed_at' => now(),
                            ]);
                        }
                        
                        Notification::make()
                            ->title('Template approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending_approval')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        // Update approval record
                        $approval = \App\Models\TemplateApproval::where('template_id', $record->id)
                            ->where('approver_id', auth()->id())
                            ->first();
                        
                        if ($approval) {
                            $approval->update([
                                'status' => 'rejected',
                                'comments' => $data['rejection_reason'],
                                'reviewed_at' => now(),
                            ]);
                        }
                        
                        Notification::make()
                            ->title('Template rejected')
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
