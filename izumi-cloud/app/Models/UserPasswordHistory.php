<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPasswordHistory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'user_password_history';

    protected $fillable = [
        'user_id',
        'email',
        'created_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function userCreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

}
