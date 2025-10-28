<?php

namespace App\Filament\Resources\PrintRequests\Schemas;

use Filament\Schemas\Schema;

class PrintRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('request_number')
                    ->label('Request Number')
                    ->default('PR-' . str_pad(\App\Models\PrintRequest::count() + 1, 6, '0', STR_PAD_LEFT))
                    ->disabled()
                    ->dehydrated(),
                
                \Filament\Forms\Components\Hidden::make('requested_by')
                    ->default(auth()->id()),
                
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'printing' => 'Printing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
                
                \Filament\Forms\Components\Repeater::make('items')
                    ->label('Print Items')
                    ->relationship('items')
                    ->schema([
                        \Filament\Forms\Components\Select::make('template_id')
                            ->label('Template')
                            ->options(\App\Models\LetterheadTemplate::approved()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $template = \App\Models\LetterheadTemplate::find($state);
                                    if ($template) {
                                        $set('template_name', $template->name);
                                    }
                                }
                            }),
                        
                        \Filament\Forms\Components\TextInput::make('template_name')
                            ->label('Template Name')
                            ->disabled()
                            ->dehydrated(false),
                        
                        \Filament\Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                        
                        \Filament\Forms\Components\TextInput::make('start_serial')
                            ->label('Start Serial Number')
                            ->numeric(),
                        
                        \Filament\Forms\Components\TextInput::make('end_serial')
                            ->label('End Serial Number')
                            ->numeric(),
                        
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->addActionLabel('Add Template')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['template_name'] ?? null)
                    ->defaultItems(1),
                
                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('Request Notes')
                    ->rows(3)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\DateTimePicker::make('approved_at')
                    ->label('Approved At'),
                
                \Filament\Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Completed At'),
            ]);
    }
}
