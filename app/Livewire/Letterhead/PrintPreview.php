<?php

namespace App\Http\Livewire\Letterhead;

use Livewire\Component;
use App\Models\LetterheadTemplate;

class PrintPreview extends Component
{
    public $templates = [];
    public $renderedContent = [];
    public $margins = [];
    public $selectedTemplateId;

    public function mount($templates, $renderedContent)
    {
        $this->templates = $templates;
        $this->renderedContent = $renderedContent;

        foreach ($templates as $template) {
            $defaults = [
                'top' => 15, 'bottom' => 15, 'left' => 15, 'right' => 15,
            ];

            $this->margins[$template->id] = array_merge(
                $defaults,
                is_array($template->print_margins) ? $template->print_margins : []
            );
        }

        // Select first template by default
        $this->selectedTemplateId = $this->templates->first()->id ?? null;
    }

    public function saveMargins($templateId)
    {
        $template = LetterheadTemplate::find($templateId);
        if ($template) {
            $template->update([
                'print_margins' => $this->margins[$templateId]
            ]);
        }

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Margins saved'
        ]);
    }

    public function render()
    {
        return view('livewire.letterhead.print-preview');
    }
}
