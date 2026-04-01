<?php

namespace Tests\Unit\Vpl;

use App\Models\Department;
use App\Models\Employee;
use App\Services\Vpl\CourseSyncService;
use App\Services\Vpl\DriverSyncService;
use Tests\TestCase;

class DriverSyncServiceTest extends TestCase
{
    /**
     * Helper to create a dummy Employee model with relations.
     */
    protected function makeEmployee(array $attrs, array $departmentIds = [], ?int $finalDeptId = null): Employee
    {
        $employee = new Employee($attrs);
        $employee->id = $attrs['id'] ?? 1;
        $employee->final_department_id = $finalDeptId;

        $depts = [];
        foreach ($departmentIds as $id) {
            $dept = new Department();
            $dept->id = $id;
            $depts[] = $dept;
        }

        $employee->setRelation('departments', collect($depts));

        return $employee;
    }

    public function test_driver_resolves_primary_dept_from_final_department_id()
    {
        $svc = new DriverSyncService();
        $employee = $this->makeEmployee(['id' => 1], [5, 6], 10);

        $deptId = $svc->resolvePrimaryDepartmentId($employee);

        $this->assertSame(10, $deptId, 'Priority 1: final_department_id should be used if present');
    }

    public function test_driver_resolves_primary_dept_from_relation_fallback()
    {
        $svc = new DriverSyncService();
        $employee = $this->makeEmployee(['id' => 1], [5, 6], null);

        $deptId = $svc->resolvePrimaryDepartmentId($employee);

        $this->assertSame(5, $deptId, 'Priority 2: first department in relation should be used next');
    }

    public function test_driver_unresolvable_department_returns_null()
    {
        $svc = new DriverSyncService();
        $employee = $this->makeEmployee(['id' => 1], [], null);

        $deptId = $svc->resolvePrimaryDepartmentId($employee);

        $this->assertNull($deptId);
    }

    public function test_driver_payload_fields_mapping()
    {
        // Not testing buildPayload directly since it queries DB, testing mapping logic
        $employee = $this->makeEmployee([
            'id' => 99,
            'employee_code' => 1001,
            'name' => 'Yamada Taro'
        ], [], 5);

        $svc = new DriverSyncService();
        $resolvedDeptId = $svc->resolvePrimaryDepartmentId($employee);

        $this->assertSame(5, $resolvedDeptId);
        $this->assertSame('LOC005', CourseSyncService::toDepartmentCode($resolvedDeptId));
        $this->assertSame('1001', (string)$employee->employee_code);
        $this->assertSame('Yamada Taro', $employee->name);
        $this->assertSame('99', (string)$employee->id);
    }
}
