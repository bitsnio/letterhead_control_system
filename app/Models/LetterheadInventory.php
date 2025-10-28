<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LetterheadInventory extends Model
{
    use HasFactory;

    protected $table = 'letterhead_inventory';

    protected $fillable = [
        'name',
        'description',
        'current_quantity',
        'minimum_level',
        'unit',
        'cost_per_unit',
        'supplier',
        'last_restocked',
        'is_active',
    ];

    protected $casts = [
        'last_restocked' => 'date',
        'is_active' => 'boolean',
        'cost_per_unit' => 'decimal:2',
    ];

    // Check if inventory is below minimum level
    public function isLowStock(): bool
    {
        return $this->current_quantity <= $this->minimum_level;
    }

    // Get stock status
    public function getStockStatusAttribute(): string
    {
        if ($this->current_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        }
        return 'in_stock';
    }
}
