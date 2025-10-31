<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
