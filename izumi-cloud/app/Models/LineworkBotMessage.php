<?php
/**
 * Created by VeHo.
 * Year: 2025-11-12
 */

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LineworkBotMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'linework_bot_messages';

    protected $fillable = [
        'message',
        'day',
        'month',
        'status',
        'message_en',
        'message_zh'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
