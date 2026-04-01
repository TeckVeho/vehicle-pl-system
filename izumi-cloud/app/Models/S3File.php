<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S3File extends Model
{
    use HasFactory;

    const FOLDER = 'folder';
    const NAME = 'name';
    const STATUS = 'status';
    const PATH = 'path';
    const LAST_MODIFIED = 'last_modified';

    const STATUS_PENDING = 0;
    const STATUS_PROCESS = 1;
    const STATUS_DONE = 2;

    protected $table = 's3_files';

    protected $fillable = [self::FOLDER, self::NAME, self::STATUS, self::PATH, self::LAST_MODIFIED];
}
