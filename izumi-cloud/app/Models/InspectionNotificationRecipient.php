<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Issue #810: 車検・定期点検の例外通知者マスタ（拠点 × ユーザ）
 */
class InspectionNotificationRecipient extends Model
{
    protected $table = 'inspection_notification_recipients';

    protected $fillable = [
        'department_id',
        'user_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
