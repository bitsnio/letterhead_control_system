<?php

namespace App\Filament\Resources\PrintResults\Schemas;

use Filament\Schemas\Schema;

class PrintResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('print_request_id')
                    ->label('Print Request')
                    ->relationship('printRequest', 'request_number')
                    ->required()
                    ->searchable(),
                
                \Filament\Forms\Components\Select::make('template_id')
                    ->label('Template')
                    ->relationship('template', 'name')
                    ->required()
                    ->searchable(),
                
                \Filament\Forms\Components\TextInput::make('requested_quantity')
                    ->label('Requested Quantity')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                
                \Filament\Forms\Components\TextInput::make('successful_prints')
                    ->label('Successful Prints')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
                
                \Filament\Forms\Components\TextInput::make('wasted_prints')
                    ->label('Wasted Prints')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
                
                \Filament\Forms\Components\Textarea::make('wastage_reason')
                    ->label('Wastage Reason')
                    ->rows(3)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\Select::make('printed_by')
                    ->label('Printed By')
                    ->relationship('printer', 'name')
                    ->default(auth()->id())
                    ->required(),
                
                \Filament\Forms\Components\DateTimePicker::make('printed_at')
                    ->label('Printed At')
                    ->default(now())
                    ->required(),
            ]);
    }
}
