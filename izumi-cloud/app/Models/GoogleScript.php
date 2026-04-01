<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleScript extends Model
{
    protected $table = 'google_scripts';
    protected $fillable = [
        'spreadsheet_id',
        'script_id',
        'status',
        'script_info',
        'last_sync_at'
    ];

    protected $casts = [
        'script_info' => 'array',
        'last_sync_at' => 'datetime'
    ];

    /**
     * Get the spreadsheet that owns the script
     */
    public function spreadsheet(): BelongsTo
    {
        return $this->belongsTo(GoogleSpreadsheet::class, 'spreadsheet_id', 'spreadsheet_id');
    }

    /**
     * Scope active scripts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by spreadsheet
     */
    public function scopeBySpreadsheet($query, $spreadsheetId)
    {
        return $query->where('spreadsheet_id', $spreadsheetId);
    }

    /**
     * Check if script is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Update last sync time
     */
    public function updateLastSync(): void
    {
        $this->update(['last_sync_at' => now()]);
    }

    /**
     * Get script info as array
     */
    public function getScriptInfoAttribute($value): array
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * Set script info as JSON
     */
    public function setScriptInfoAttribute($value): void
    {
        $this->attributes['script_info'] = is_array($value) ? json_encode($value) : $value;
    }
}
