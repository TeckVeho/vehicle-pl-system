<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleAuthLog extends Model
{
    use HasFactory;

    protected $table = 'google_auth_logs';
    protected $fillable = [
        'email',
        'name',
        'google_id',
        'picture',
        'action',
        'token_info',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'token_info' => 'array'
    ];

    /**
     * Get the user's email
     */
    public function getEmailAttribute($value)
    {
        return $value;
    }

    /**
     * Get the user's name
     */
    public function getNameAttribute($value)
    {
        return $value;
    }

    /**
     * Get the Google ID
     */
    public function getGoogleIdAttribute($value)
    {
        return $value;
    }

    /**
     * Get the user's picture URL
     */
    public function getPictureAttribute($value)
    {
        return $value;
    }

    /**
     * Get the action performed
     */
    public function getActionAttribute($value)
    {
        return $value;
    }

    /**
     * Get the token information
     */
    public function getTokenInfoAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true);
    }

    /**
     * Get the IP address
     */
    public function getIpAddressAttribute($value)
    {
        return $value;
    }

    /**
     * Get the user agent
     */
    public function getUserAgentAttribute($value)
    {
        return $value;
    }

    /**
     * Scope to filter by email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope to filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get recent logs for an email
     */
    public static function getRecentLogsByEmail($email, $limit = 10)
    {
        return self::where('email', $email)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all unique emails that have logged in
     */
    public static function getUniqueEmails()
    {
        return self::select('email')
            ->distinct()
            ->orderBy('email')
            ->pluck('email');
    }

    /**
     * Get login statistics
     */
    public static function getLoginStats()
    {
        return self::selectRaw('
            COUNT(*) as total_logins,
            COUNT(DISTINCT email) as unique_users,
            DATE(created_at) as login_date
        ')
        ->groupBy('login_date')
        ->orderBy('login_date', 'desc')
        ->get();
    }
}
