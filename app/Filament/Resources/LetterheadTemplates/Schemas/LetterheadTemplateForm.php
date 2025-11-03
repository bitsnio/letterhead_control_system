<?php

namespace App\Filament\Resources\LetterheadTemplates\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Str;

class LetterheadTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Template Name')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category')
                            ->options([
                                'certificate' => 'Certificate',
                                'letter' => 'Letter',
                                'invoice' => 'Invoice',
                                'report' => 'Report',
                                'other' => 'Other',
                            ])
                            ->searchable()
                            ->preload()
                            ->label('Category'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->disabled(fn($record) => $record && $record->approval_status !== 'approved'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->label('Description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Template Content')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->label('Template Content')
                            ->helperText('Use $VariableName$ format to add variables that can be replaced during printing. Example: $Student_Name$, $Date$, $Amount$')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->columnSpanFull(),

                        TextEntry::make('detected_variables')
                            ->label('Detected Variables')
                            ->state(function ($get) {
                                $content = $get('content');
                                if (!$content) {
                                    return 'No variables detected';
                                }

                                preg_match_all('/\$([a-zA-Z0-9_]+)\$/', $content, $matches);
                                $variables = array_unique($matches[1]);

                                if (empty($variables)) {
                                    return 'No variables detected';
                                }

                                return implode(', ', array_map(fn($var) => "\$$var\$", $variables));
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Approval Information')
                    ->schema([
                        TextEntry::make('approval_status')
                            ->label('Approval Status')
                            ->state(fn($record) => $record ? Str::headline($record->approval_status) : 'Pending'),

                        TextEntry::make('created_by')
                            ->label('Created By')
                            ->state(fn($record) => $record?->createdBy?->name ?? 'N/A'),

                        TextEntry::make('approved_by')
                            ->label('Approved By')
                            ->state(fn($record) => $record?->approvedBy?->name ?? 'Not approved yet'),

                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->state(function ($record): string {
                                // Return a raw string immediately
                                return $record?->approved_at?->format('M d, Y H:i') ?? 'Not approved yet';
                            })
                            ->dateTime(false),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record !== null),
            ]);
    }
}
