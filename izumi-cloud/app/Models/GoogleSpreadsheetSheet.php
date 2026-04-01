<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleSpreadsheetSheet extends Model
{
    protected $table = 'google_spreadsheet_sheets';
    protected $fillable = [
        'spreadsheet_id',
        'sheet_id',
        'department_id',
        'title',
        'last_sync_at',
    ];

    protected $casts = [
        'department_id' => 'integer',
    ];

    /**
     * Relationship với spreadsheet
     */
    public function spreadsheet(): BelongsTo
    {
        return $this->belongsTo(GoogleSpreadsheet::class, 'spreadsheet_id', 'spreadsheet_id');
    }


    /**
     * Scope để lọc theo department
     */
    public function scopeByDepartment($query, string $departmentName)
    {
        return $query->where('department_name', $departmentName);
    }

    /**
     * Scope để lọc theo thời gian (ngày hôm nay)
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope để lọc theo thời gian (tuần này)
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope để lọc theo thời gian (tháng này)
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
