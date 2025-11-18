<?php
namespace App\Filament\Resources\LetterheadTemplates\Pages;

use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class PrintLetterheadTemplate extends ViewRecord
{
    protected static string $resource = LetterheadTemplateResource::class;

    protected string $view = 'filament.resources.letterhead-template-resource.pages.print-letterhead-template';

    public $variableValues = [];

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Initialize variable values
        if (!empty($this->record->variables)) {
            foreach ($this->record->variables as $variable) {
                $this->variableValues[$variable] = '';
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to Templates')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    public function generatePrint()
    {
        // Validate that all variables have values
        $missingVariables = [];
        foreach ($this->record->variables as $variable) {
            if (empty($this->variableValues[$variable])) {
                $missingVariables[] = $variable;
            }
        }

        if (!empty($missingVariables)) {
            Notification::make()
                ->danger()
                ->title('Missing Variables')
                ->body('Please fill in all variables: ' . implode(', ', $missingVariables))
                ->send();
            return;
        }

        // Generate the final content
        $content = $this->record->content;
        foreach ($this->variableValues as $variable => $value) {
            $content = str_replace('$' . $variable . '$', $value, $content);
        }

        // Store in session for print preview
        session(['print_content' => $content]);

        Notification::make()
            ->success()
            ->title('Ready to Print')
            ->body('The document is ready for printing.')
            ->send();

        // Redirect to print preview
        return redirect()->route('filament.admin.pages.print-preview');
    }

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;
        
        if (!$record) {
            return false;
        }

        // Only allow access to approved and active templates
        return $record->approval_status === 'approved' && $record->is_active;
    }
}