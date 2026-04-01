<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-07-19
 */

namespace Repository;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Foundation\Application;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    protected $userRepository;

    public function __construct(Application $app, UserRepositoryInterface $userRepository)
    {
        parent::__construct($app);
        $this->userRepository = $userRepository;
    }

    /**
     * Instantiate model
     *
     * @param  Department  $model
     */
    public function model()
    {
        return Department::class;
    }

    public function index()
    {
        return Department::query()->orderBy('position', 'ASC')->get();
    }

    public function changeOrder($listDp)
    {
        foreach ($listDp as $key => $dp) {
            $department = Department::query()->find($dp);
            $department->position = $key + 1;
            $department->save();
        }

        return Department::query()->orderBy('position', 'ASC')->get();
    }

    public function updateDepartment($attributes, $id)
    {
        $department = Department::query()->find($id);
        if ($department) {
            $department->update($attributes);

            return $department;
        }

        return null;
    }

    public function findById($id)
    {
        return Department::query()->find($id);
    }

    public function exportCsv()
    {
        $listMemberLW = $this->userRepository->getListMemberLW();
        $data = Department::query()->with(['chiefOperationsManagerEmployees'])
            ->orderBy('position', 'ASC')
            ->get();
        // Preload tất cả employees 1 lần (tránh N+1 query)
        Department::preloadEmployees($data);

        $result = $data->map(function ($item) use ($listMemberLW) {
            if (! $item) {
                return null;
            }
            $item->chief_operations_manager_employees = '';
            if ($item->chief_operations_manager) {
                $item->chief_operations_manager_employees = $this->getEmployeeName($item->chief_operations_manager);
            }
            // Sử dụng accessor từ model (đã được preload)
            $item->operations_manager_assistant_employee = $item->operations_manager_assistant_employees;
            $item->operations_manager_appointment_employees = $item->operations_manager_appointment_employees;
            $item->maintenance_manager_appointment_employees = $item->maintenance_manager_appointment_employees;
            $item->maintenance_manager_assistant_employees = $item->maintenance_manager_assistant_employees;
            $item->interview_pic = $this->getInterviewPic($item->interview_pic);
            $item->interview_pic_line_work = $this->getInterviewPicLineWork($item->interview_pic_line_work, $listMemberLW);

            return $item;
        })->filter();

        // Clear cache sau khi dùng xong
        Department::clearPreloadedEmployees();

        return $result;
    }

    public function getInterviewPic($userCode)
    {
        $user = User::query()
            ->where('id', $userCode)
            ->role([ROLE_AM_SM, ROLE_QUALITY_CONTROL, ROLE_SITE_MANAGER, ROLE_HQ_MANAGER, ROLE_TL, ROLE_DEPARTMENT_OFFICE_STAFF])
            ->select('uuid', 'id as code')
            ->selectRaw("CONCAT(`id`,' - ', `name`) AS name_code")
            ->first();

        return $user ? $user->name_code : null;
    }

    public function getInterviewPicLineWork($interviewPicLineWork, $listMemberLW)
    {

        if (! $interviewPicLineWork) {
            return '';
        }

        // Decode JSON string to array if needed
        $codes = is_string($interviewPicLineWork)
            ? json_decode($interviewPicLineWork, true)
            : $interviewPicLineWork;

        if (! is_array($codes) || empty($codes)) {
            return '';
        }

        // Create a map of code => full_name from $listMemberLW
        $memberMap = [];
        foreach ($listMemberLW as $member) {
            if (isset($member->code) || isset($member['code'])) {
                $code = is_object($member) ? $member->code : $member['code'];
                $fullName = is_object($member) ? $member->full_name : $member['full_name'];
                $memberMap[$code] = $fullName;
            }
        }

        // Map codes to full_name
        $fullNames = [];
        foreach ($codes as $code) {
            if (isset($memberMap[$code])) {
                $fullNames[] = $memberMap[$code];
            }
        }

        return ! empty($fullNames) ? implode(', ', $fullNames) : '';
    }

    public function getEmployeeName($employeeId)
    {
        $employee = Employee::query()
            ->where('id', $employeeId)
            ->select('id', 'name')
            ->first();

        return $employee ? $employee->name : '';
    }
}
