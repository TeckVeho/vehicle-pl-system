<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laratrust\Traits\LaratrustUserTrait;
use DateTimeInterface;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    use AuthenticableTrait;
    use SoftDeletes;
    use HasRoles;

    protected $primaryKey = 'uuid';

    protected $table = 'users';
    protected $guard_name = 'api';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'role',
        'password',
        'supervisor_email',
        'department_code',
        'email',
        'expected_retirement_date',
        'assign_vehicle_personnel',
        'language'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'images' => 'json',
    ];



    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getAttribute('id');
        //return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'guard' => 'api'
        ];
    }

    public function getAuthIdentifierName()
    {
        return "id";
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function images()
    {
        return $this->belongsTo(Image::class, 'image_id')->select(['id', 'title', 'url']);
    }


    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'department_code');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'employee_code', 'id');
    }

    public function user_contacts()
    {
        return $this->hasOne(UserContacts::class, 'user_id', 'id');
    }

    public function userPasswordHistory()
    {
        return $this->hasMany(UserPasswordHistory::class, 'user_id', 'id');
    }
}
