<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\DepartmentRoleDivision;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use Illuminate\Support\Arr;

class BaseDivisionImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $roles = [];
        $departments = [];
        
        foreach ($collection as $key => $value) {
            if ($key == 0) {
                // Dòng đầu tiên - lấy roles từ cột 2 trở đi
                for ($i = 2; $i < count($value); $i++) {
                    if (!empty(trim($value[$i]))) {
                        $roles[] = trim($value[$i]);
                    }
                }
            } else {
                // Các dòng tiếp theo - lấy department từ cột 1
                $departmentName = trim($value[1]);
                if (!empty($departmentName)) {
                    $department = Department::where('name', $departmentName)->first();
                    for ($i = 1; $i < count($value); $i++) {
                        if (isset($roles[$i-1]) && !empty(trim($value[$i]))) {
                            $roleName = $roles[$i-1];
                            $divisionValue = trim($value[$i+1]);
                            $role = Role::where('display_name', $roleName)->first();
                            if ($role) {
                                if($department){
                                    DepartmentRoleDivision::updateOrCreate(
                                        [
                                            'department_id' => $department->id,
                                            'role_id' => $role->id
                                        ],
                                        [
                                            'division' => $divisionValue
                                        ]
                                    );
                                    Log::info("Created/Updated: DepartmentRoleDivision for {$department->name} - {$roleName} - {$divisionValue}");
                                }
                            } else {
                                Log::warning("Role not found: {$roleName}");
                            }
                        }
                    }
                }
            }
        }
    }
}
