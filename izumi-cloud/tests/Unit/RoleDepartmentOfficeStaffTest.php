<?php

namespace Tests\Unit;

use Tests\TestCase;

class RoleDepartmentOfficeStaffTest extends TestCase
{
    /**
     * Constant ROLE_DEPARTMENT_OFFICE_STAFF is defined and equals 'department_office_staff'.
     */
    public function test_role_department_office_staff_constant_is_defined(): void
    {
        $this->assertTrue(defined('ROLE_DEPARTMENT_OFFICE_STAFF'));
        $this->assertSame('department_office_staff', ROLE_DEPARTMENT_OFFICE_STAFF);
    }
}
