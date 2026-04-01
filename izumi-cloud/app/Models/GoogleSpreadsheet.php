<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleSpreadsheet extends Model
{

    protected $table = 'google_spreadsheets';
    protected $fillable = [
        'spreadsheet_id',
        'folder_id',
        'year',
        'last_sync_at',
        'sync_status'
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'year' => 'integer',
    ];

    /**
     * Relationship với sync logs
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(GoogleSpreadsheetSheet::class, 'spreadsheet_id', 'spreadsheet_id');
    }

    /**
     * Scope để lọc theo năm-tháng
     */
    public function scopeByYearMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope để lọc theo trạng thái sync
     */
    public function scopeBySyncStatus($query, string $status)
    {
        return $query->where('sync_status', $status);
    }

    /**
     * Lấy URL của spreadsheet
     */
    public function getSpreadsheetUrlAttribute(): string
    {
        return "https://docs.google.com/spreadsheets/d/{$this->spreadsheet_id}/edit";
    }

    /**
     * Kiểm tra xem spreadsheet có cần sync không
     */
    public function needsSync(): bool
    {
        return $this->sync_status === 'pending' ||
            $this->sync_status === 'failed' ||
            $this->last_sync_at === null ||
            $this->last_sync_at->diffInHours(now()) > 24;
    }

    /**
     * Đánh dấu đang sync
     */
    public function markAsSyncing(): void
    {
        $this->update(['sync_status' => 'syncing']);
    }

    /**
     * Đánh dấu sync thành công
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'sync_status' => 'completed',
            'last_sync_at' => now()
        ]);
    }

    /**
     * Đánh dấu sync thất bại
     */
    public function markAsFailed(): void
    {
        $this->update(['sync_status' => 'failed']);
    }
}
