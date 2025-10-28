<?php

namespace App\Filament\Resources\LetterheadInventories\Schemas;

use Filament\Schemas\Schema;

class LetterheadInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                \Filament\Forms\Components\Textarea::make('description')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\TextInput::make('current_quantity')
                    ->numeric()
                    ->default(0)
                    ->required(),
                
                \Filament\Forms\Components\TextInput::make('minimum_level')
                    ->numeric()
                    ->default(0)
                    ->required(),
                
                \Filament\Forms\Components\TextInput::make('unit')
                    ->default('pieces')
                    ->maxLength(50),
                
                \Filament\Forms\Components\TextInput::make('cost_per_unit')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$'),
                
                \Filament\Forms\Components\TextInput::make('supplier')
                    ->maxLength(255),
                
                \Filament\Forms\Components\DatePicker::make('last_restocked'),
                
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
