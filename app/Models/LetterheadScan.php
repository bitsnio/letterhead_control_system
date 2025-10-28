<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterheadScan extends Model
{
    use HasFactory;

    protected $table = 'letterhead_scans';

    protected $fillable = [
        'print_result_id',
        'scan_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'description',
        'uploaded_by',
    ];

    // Relationships
    public function printResult(): BelongsTo
    {
        return $this->belongsTo(PrintResult::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ScanReview::class, 'scan_id');
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('scan_type', 'successful');
    }

    public function scopeWasted($query)
    {
        return $query->where('scan_type', 'wasted');
    }

    // Get file size in human readable format
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Check if scan is successful
    public function isSuccessful(): bool
    {
        return $this->scan_type === 'successful';
    }

    // Check if scan is wasted
    public function isWasted(): bool
    {
        return $this->scan_type === 'wasted';
    }
}
