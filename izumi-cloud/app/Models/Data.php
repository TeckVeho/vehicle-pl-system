<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-21
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Data extends Model
{
    use HasFactory;
    use SoftDeletes;

    const NAME = 'name';
    const FROM = 'from';
    const TO = 'to';
    const REMARK = 'remark';
    const FILE_NAME_MAP = 'file_name_map';

    protected $table = 'datas';

    protected $fillable = ['name', 'from', 'to', 'remark','file_name_map'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'name' => 'string',
        'from' => 'integer',
        'to' => 'integer',
        'remark' => 'string'
    ];

    public function dataItem() {
        return $this->hasMany('App\Models\DataItem');
    }

    public function from() {
        return $this->hasOne('App\Models\System', 'id', 'from');
    }

    public function to() {
        return $this->hasOne('App\Models\System', 'id', 'to');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
