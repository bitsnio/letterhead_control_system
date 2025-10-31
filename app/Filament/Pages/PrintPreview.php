<?php
namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;

class PrintPreview extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-printer';

    protected string $view = 'filament.pages.print-preview';

    protected static ?string $title = 'Print Preview';

    protected static bool $shouldRegisterNavigation = false;

    public $content = '';

    public function mount(): void
    {
        $this->content = session('print_content', '');
        
        if (empty($this->content)) {
            $this->redirect(route('filament.admin.resources.print-templates.index'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->action('printDocument'),
            
            Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(route('filament.admin.resources.print-templates.index')),
        ];
    }

    public function printDocument()
    {
        $this->dispatch('print-document');
    }
}