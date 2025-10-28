<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                \Filament\Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(\App\Models\User::class, 'email', ignoreRecord: true),
                
                \Filament\Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8)
                    ->same('passwordConfirmation')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                
                \Filament\Forms\Components\TextInput::make('passwordConfirmation')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8)
                    ->dehydrated(false),
                
                \Filament\Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'manager' => 'Manager',
                        'staff' => 'Staff',
                        'printer' => 'Printer',
                    ])
                    ->default('staff')
                    ->required(),
                
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
                
                \Filament\Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At'),
            ]);
    }
}
