<?php

namespace App\Filament\Resources\LetterheadInventories\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class LetterheadInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                [
                    // --- Batch Information Section ---
                    Section::make('Batch Information')
                        ->schema([
                            Forms\Components\TextInput::make('batch_name')
                                ->required()
                                ->maxLength(255)
                                ->label('Batch Name'),

                            Forms\Components\DatePicker::make('received_date')
                                ->required()
                                ->default(now())
                                ->label('Received Date'),

                            Forms\Components\TextInput::make('supplier')
                                ->maxLength(255)
                                ->label('Supplier'),
                        ])
                        ->columns(3),

                    // --- Serial Numbers Section ---
                    Section::make('Serial Numbers')
                        ->schema([
                            Forms\Components\TextInput::make('start_serial')
                                ->required()
                                ->numeric()
                                ->label('Start Serial Number')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $end = $get('end_serial');
                                    if ($state && $end && $end >= $state) {
                                        $set('quantity', ($end - $state) + 1);
                                    } else {
                                        $set('quantity', null);
                                    }
                                }),

                            Forms\Components\TextInput::make('end_serial')
                                ->required()
                                ->numeric()
                                ->label('End Serial Number')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $start = $get('start_serial');
                                    if ($start && $state && $state >= $start) {
                                        $set('quantity', ($state - $start) + 1);
                                    } else {
                                        $set('quantity', null);
                                    }
                                })
                                ->rule(function (callable $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $start = $get('start_serial');
                                        if ($start && $value < $start) {
                                            $fail('The end serial must be greater than or equal to the start serial.');
                                        }
                                    };
                                }),

                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->label('Quantity')
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columns(3),

                    // --- Additional Notes Section ---
                    Section::make('Additional Notes')
                        ->schema([
                            Forms\Components\Textarea::make('notes')
                                ->rows(3)
                                ->maxLength(65535)
                                ->label('Notes'),
                        ]),

                    // --- Status / Active Toggle Section ---
                    Section::make('Status')
                        ->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ]),
                ]
            );
    }
}
