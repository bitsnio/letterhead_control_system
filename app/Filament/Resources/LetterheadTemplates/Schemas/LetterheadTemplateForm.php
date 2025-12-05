<?php

namespace App\Filament\Resources\LetterheadTemplates\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Str;
use Filament\Actions\Action;



class LetterheadTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Template Details Section
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
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Full Width Template Content Section
                Section::make('Template Content')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->label('Template Content')
                            ->helperText('Use $VariableName$ format to add variables that can be replaced during printing. Example: $GD_NO$, $Date$, $INVOICE_NO$')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'codeBlock',
                                'table',
                                'redo',
                                'undo',
                                'alignJustify'
                            ])
                            ->disableToolbarButtons([
                                'attachFiles',
                            ])
                            ->columnSpanFull()
                            ->extraAttributes(['style' => 'min-height: 500px;'])
                            ->hintActions([
                                Action::make('preview')
                                    ->label('Preview Template')
                                    ->icon('heroicon-o-eye')
                                    ->color('primary')
                                    ->modalHeading('Template Preview')
                                    ->modalWidth('7xl')
                                    ->modalContent(function ($get) {
                                        $content = $get('content');
                                        $margins = $get('print_margins') ?? [
                                            'top' => '15',
                                            'right' => '15',
                                            'bottom' => '15',
                                            'left' => '15',
                                            'orientation' => 'portrait',
                                            'font_size' => 100
                                        ];
                                        
                                        $orientation = $margins['orientation'] ?? 'portrait';
                                        $fontSize = $margins['font_size'] ?? 100;
                                        $isLandscape = $orientation === 'landscape';
                                        
                                        return view('filament.components.template-preview', [
                                            'content' => $content,
                                            'margins' => $margins,
                                            'orientation' => $orientation,
                                            'fontSize' => $fontSize,
                                            'isLandscape' => $isLandscape,
                                        ]);
                                    })
                                    ->modalSubmitAction(false)
                                    ->modalCancelActionLabel('Close'),
                            ]),

                        TextEntry::make('detected_variables')
                            ->label('Detected Variables')
                            ->state(function ($get) {
                                $content = $get('content');
                                if (!$content) {
                                    return view('filament.components.no-variables-badge');
                                }

                                preg_match_all('/\$([a-zA-Z0-9_]+)\$/', $content, $matches);
                                $variables = array_unique($matches[1]);

                                if (empty($variables)) {
                                    return view('filament.components.no-variables-badge');
                                }

                                return view('filament.components.variables-list', [
                                    'variables' => $variables
                                ]);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full'),

                // Approval Information Section
                Section::make('Approval Information')
                    ->schema([
                        TextEntry::make('approval_status')
                            ->label('Approval Status')
                            ->state(fn($record) => $record ? Str::headline($record->approval_status) : 'Pending')
                            ->badge()
                            ->color(fn($record) => match($record?->approval_status) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('created_by')
                            ->label('Created By')
                            ->state(fn($record) => $record?->createdBy?->name ?? 'N/A'),

                        TextEntry::make('approved_by')
                            ->label('Approved By')
                            ->state(fn($record) => $record?->approvedBy?->name ?? 'Not approved yet'),

                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->dateTime('M d, Y H:i')
                            ->formatStateUsing(fn($state) => $state ? $state : 'Not approved yet'),

                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record !== null)
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }
}