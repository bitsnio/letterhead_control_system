<?php

namespace App\Filament\Pages;

use App\Models\PrintResult;
use App\Models\PrintRequest;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class PrintResultsManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.print-results-management';

    protected static ?string $navigationLabel = 'Print Results Management';

    protected static ?string $title = 'Print Results Management';

    public function table(Table $table): Table
    {
        return $table
            ->query(PrintResult::query()->with(['printRequest', 'template', 'printer', 'assignedUser']))
            ->columns([
                TextColumn::make('printRequest.request_number')
                    ->label('Request #')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('requested_quantity')
                    ->label('Requested Qty')
                    ->sortable(),
                
                TextColumn::make('successful_prints')
                    ->label('Successful')
                    ->color('success')
                    ->sortable(),
                
                TextColumn::make('wasted_prints')
                    ->label('Wasted')
                    ->color('danger')
                    ->sortable(),
                
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ]),
                
                TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->placeholder('Not assigned'),
                
                TextColumn::make('printer.name')
                    ->label('Printed By')
                    ->sortable(),
                
                TextColumn::make('printed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('assigned_to')
                    ->relationship('assignedUser', 'name'),
            ])
            ->actions([
                Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->form([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                        
                        Select::make('assigned_to')
                            ->label('Assign To')
                            ->options(\App\Models\User::where('is_active', true)->pluck('name', 'id'))
                            ->searchable(),
                        
                        Textarea::make('status_notes')
                            ->label('Status Notes')
                            ->rows(3),
                    ])
                    ->action(function (PrintResult $record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                            'assigned_to' => $data['assigned_to'] ?? null,
                            'status_notes' => $data['status_notes'] ?? null,
                            'status_updated_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Print result status updated')
                            ->success()
                            ->send();
                    }),
                
                Action::make('assign_to_me')
                    ->label('Assign to Me')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn (PrintResult $record) => !$record->assigned_to)
                    ->action(function (PrintResult $record) {
                        $record->update([
                            'assigned_to' => auth()->id(),
                            'status' => 'in_progress',
                            'status_updated_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Print result assigned to you')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                // Bulk actions can be added here
            ]);
    }
}
