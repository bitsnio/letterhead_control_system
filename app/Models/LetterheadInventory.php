<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterheadInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_name',
        'start_serial',
        'end_serial',
        'quantity',
        'received_date',
        'supplier',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'start_serial' => 'integer',
        'end_serial' => 'integer',
        'quantity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($letterhead) {
            // Auto-calculate quantity based on serial range
            if ($letterhead->start_serial && $letterhead->end_serial) {
                $letterhead->quantity = ($letterhead->end_serial - $letterhead->start_serial) + 1;
            }
        });
    }
    public function serialUsages(): HasMany
    {
        return $this->hasMany(SerialUsage::class);
    }

    public function getUsedSerialsAttribute(): array
    {
        return $this->serialUsages()
            ->pluck('serial_number')
            ->toArray();
    }

    public function getAvailableSerialsAttribute(): array
    {
        $usedSerials = $this->usedSerials;
        $allSerials = range($this->start_serial, $this->end_serial);

        return array_diff($allSerials, $usedSerials);
    }

    public function hasAvailableSerials(int $quantity): bool
    {
        return count($this->availableSerials) >= $quantity;
    }

    public function getNextAvailableSerial(): ?int
    {
        $availableSerials = $this->availableSerials;
        return !empty($availableSerials) ? min($availableSerials) : null;
    }

    public function validateSerialRange(int $startSerial, int $endSerial): array
    {
        $errors = [];

        // Check if serials are within batch range
        if ($startSerial < $this->start_serial || $endSerial > $this->end_serial) {
            $errors[] = "Serial range must be between {$this->start_serial} and {$this->end_serial}";
        }

        // Check if start is less than or equal to end
        if ($startSerial > $endSerial) {
            $errors[] = "Start serial must be less than or equal to end serial";
        }

        // Check if any serial in range is already used
        $requestedSerials = range($startSerial, $endSerial);
        $usedSerials = $this->usedSerials;
        $conflictingSerials = array_intersect($requestedSerials, $usedSerials);

        if (!empty($conflictingSerials)) {
            $errors[] = "Some serials are already used: " . implode(', ', $conflictingSerials);
        }

        return $errors;
    }

    public function allocateSerials(PrintJob $printJob, int $startSerial, int $endSerial): bool
    {
        $errors = $this->validateSerialRange($startSerial, $endSerial);

        if (!empty($errors)) {
            return false;
        }

        $serials = range($startSerial, $endSerial);

        foreach ($serials as $serial) {
            SerialUsage::create([
                'letterhead_inventory_id' => $this->id,
                'print_job_id' => $printJob->id,
                'serial_number' => $serial,
                'used_at' => now(),
            ]);
        }

        // Update used quantity
        $this->increment('used_quantity', count($serials));

        return true;
    }

    // public function allocateSerialsWithTemplates(PrintJob $printJob, array $templatesData): bool
    // {
    //     $startSerial = $printJob->start_serial;
    //     $currentSerial = $startSerial;

    //     foreach ($templatesData as $templateId => $templateData) {
    //         $quantity = $templateData['quantity'] ?? 0;

    //         if ($quantity > 0) {
    //             // Validate this template's serial range
    //             $templateStartSerial = $currentSerial;
    //             $templateEndSerial = $currentSerial + $quantity - 1;

    //             $errors = $this->validateSerialRange($templateStartSerial, $templateEndSerial);
    //             if (!empty($errors)) {
    //                 return false;
    //             }

    //             // Create serial usages for this template
    //             for ($i = 0; $i < $quantity; $i++) {
    //                 SerialUsage::create([
    //                     'letterhead_inventory_id' => $this->id,
    //                     'print_job_id' => $printJob->id,
    //                     'letterhead_template_id' => $templateId, // Store template ID
    //                     'serial_number' => $currentSerial,
    //                     'used_at' => now(),
    //                 ]);
    //                 $currentSerial++;
    //             }
    //         }
    //     }

    //     // Update used quantity
    //     $totalQuantity = $printJob->quantity;
    //     $this->increment('used_quantity', $totalQuantity);

    //     return true;
    // }

    // In LetterheadInventory.php model
    public function allocateSerialsWithTemplates(PrintJob $printJob, array $templatesData): bool
    {
        $currentSerial = $printJob->start_serial;

        foreach ($templatesData as $templateId => $templateData) {
            $quantity = $templateData['quantity'] ?? 0;

            if ($quantity > 0) {
                // Validate this template's serial range
                $templateStartSerial = $currentSerial;
                $templateEndSerial = $currentSerial + $quantity - 1;

                $errors = $this->validateSerialRange($templateStartSerial, $templateEndSerial);
                if (!empty($errors)) {
                    return false;
                }

                // Create serial usages for this template
                for ($i = 0; $i < $quantity; $i++) {
                    SerialUsage::create([
                        'letterhead_inventory_id' => $this->id,
                        'print_job_id' => $printJob->id,
                        'letterhead_template_id' => $templateId,
                        'serial_number' => $currentSerial,
                        'used_at' => now(),
                    ]);
                    $currentSerial++;
                }
            }
        }

        // Update used quantity
        $totalQuantity = $printJob->quantity;
        $this->increment('used_quantity', $totalQuantity);

        return true;
    }
}
