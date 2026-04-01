<?php
/**
 * Created by VeHo.
 * Year: 2022-01-04
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'departments';

    /**
     * Cache để lưu preloaded employees (tránh N+1)
     */
    protected static $preloadedEmployees = null;

    protected $fillable = [
        'id',
        'name',
        'position',
        'address',
        'province_name',
        'province_md5',
        'interview_address',
        'interview_address_url',
        'path_for_interview_address',
        'interview_pic',
        'interview_pic_line_work',
        'post_code',
        'tel',
        'office_name',
        'office_location',
        'office_area',
        'rest_room_area',
        'garage_location_1',
        'garage_area_1',
        'garage_location_2',
        'garage_area_2',
        'operations_manager_appointment',
        'operations_manager_assistant',
        'maintenance_manager_appointment',
        'maintenance_manager_assistant',
        'maintenance_manager_phone_number',
        'maintenance_manager_fax_number',
        'truck_association_membership_number',
        'g_mark_number',
        'g_mark_expiration_date',
        'it_roll_call',
        'g_mark_action_radio',
        'chief_operations_manager',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array',
        'interview_pic_line_work' => 'array',
        'operations_manager_assistant' => 'array',
        'maintenance_manager_appointment' => 'array',
        'operations_manager_appointment' => 'array',
        'maintenance_manager_assistant' => 'array',
        'g_mark_action_radio' => 'array',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Preload employees cho collection departments (tránh N+1 query)
     * Gọi method này trước khi truy cập các accessor employees
     *
     * @param \Illuminate\Support\Collection $departments
     */
    public static function preloadEmployees($departments)
    {
        $managerFields = [
            'operations_manager_assistant',
            'maintenance_manager_appointment',
            'operations_manager_appointment',
            'maintenance_manager_assistant'
        ];

        $allEmployeeIds = collect();
        foreach ($departments as $department) {
            foreach ($managerFields as $field) {
                $ids = $department->$field;
                if (!empty($ids) && is_array($ids)) {
                    $allEmployeeIds = $allEmployeeIds->merge($ids);
                }
            }
        }

        static::$preloadedEmployees = $allEmployeeIds->unique()->isNotEmpty()
            ? Employee::select('id', 'name')
                ->whereIn('id', $allEmployeeIds->unique())
                ->get()
                ->keyBy('id')
            : collect();
    }

    /**
     * Clear preloaded employees cache
     */
    public static function clearPreloadedEmployees()
    {
        static::$preloadedEmployees = null;
    }

    /**
     * Lấy employees từ cache hoặc query trực tiếp
     */
    protected function getEmployeesFromIds($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return collect();
        }

        // Nếu đã preload, lấy từ cache
        if (static::$preloadedEmployees !== null) {
            return static::$preloadedEmployees->only($ids)->values();
        }

        // Fallback: query trực tiếp (cho trường hợp gọi đơn lẻ)
        return Employee::select('id', 'name')
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Accessor: Operations Manager Assistant Employees
     */
    public function getOperationsManagerAssistantEmployeesAttribute()
    {
        return $this->getEmployeesFromIds($this->operations_manager_assistant);
    }

    /**
     * Accessor: Maintenance Manager Appointment Employees
     */
    public function getMaintenanceManagerAppointmentEmployeesAttribute()
    {
        return $this->getEmployeesFromIds($this->maintenance_manager_appointment);
    }

    /**
     * Accessor: Operations Manager Appointment Employees
     */
    public function getOperationsManagerAppointmentEmployeesAttribute()
    {
        return $this->getEmployeesFromIds($this->operations_manager_appointment);
    }

    /**
     * Accessor: Maintenance Manager Assistant Employees
     */
    public function getMaintenanceManagerAssistantEmployeesAttribute()
    {
        return $this->getEmployeesFromIds($this->maintenance_manager_assistant);
    }

    public function chiefOperationsManagerEmployees()
    {
        return  $this->belongsTo(Employee::class, 'chief_operations_manager','id');
    }
}
