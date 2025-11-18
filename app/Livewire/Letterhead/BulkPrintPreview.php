<?php

namespace App\Livewire\Letterhead;

use Livewire\Component;

class BulkPrintPreview extends Component
{
    public function render()
    {
        return view('livewire.letterhead.bulk-print-preview');
    }

    // public function getSerialInfo(): array
    // {
    //     $totalQuantity = array_sum($this->quantities);
    //     $allStartSerials = array_filter($this->startSerials);
    //     $allEndSerials = array_filter($this->endSerials);

    //     if (empty($allStartSerials) || empty($allEndSerials)) {
    //         return [
    //             'quantity' => $totalQuantity,
    //             'serial_display' => 'Not set'
    //         ];
    //     }

    //     $startSerial = min($allStartSerials);
    //     $endSerial = max($allEndSerials);

    //     return [
    //         'quantity' => $totalQuantity,
    //         'serial_display' => $startSerial . ' - ' . $endSerial
    //     ];
    // }

    // public function getTemplateQuantity($templateId): int
    // {
    //     return $this->quantities[$templateId] ?? 1;
    // }

    // public function getTemplateSerialRange($templateId): string
    // {
    //     $start = $this->startSerials[$templateId] ?? null;
    //     $end = $this->endSerials[$templateId] ?? null;

    //     if ($start && $end) {
    //         return $start === $end ? $start : $start . ' - ' . $end;
    //     }

    //     return 'Not set';
    // }
}
