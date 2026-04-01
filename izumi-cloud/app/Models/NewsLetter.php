<?php
/**
 * Created by VeHo.
 * Year: 2026-03-30
 */

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsLetter extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'news_letters';

    protected $fillable = [
        'title',
        'status',
        'position',
        'file_id',
        'year',
        'month',
        'created_by',
        'updated_by',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
