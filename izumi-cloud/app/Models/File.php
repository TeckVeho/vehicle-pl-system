<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory;
    use SoftDeletes;

    const FILE_NAME = "file_name";
    const UUID = "uuid";
    const FILE_EXTENSION = "file_extension";
    const FILE_PATH = "file_path";
    const FILE_SIZE = "file_size";
    const DATA_ITEM_ID = "data_item_id";
    const FILE_URL = "file_url";
    const FILE_SYS_DISK = "file_sys_disk";
    const EXPIRED_AT = "expired_at";

    protected $table = 'files';
    protected $fillable = [
        self::UUID,
        self::FILE_NAME,
        self::FILE_EXTENSION,
        self::FILE_PATH,
        self::FILE_SIZE,
        self::FILE_URL,
        self::FILE_SYS_DISK,
        self::EXPIRED_AT,
    ];
    protected $hidden = ['pivot'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    protected $appends = array('url_view_file');

    public function getUrlViewFileAttribute()
    {
        return url('/') . '/view-file/' . $this->uuid . '/' . $this->file_name;
    }
}
