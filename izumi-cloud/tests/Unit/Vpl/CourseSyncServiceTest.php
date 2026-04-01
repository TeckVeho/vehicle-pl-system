<?php

namespace Tests\Unit\Vpl;

use App\Models\Course;
use App\Models\Department;
use App\Services\Vpl\CourseSyncService;
use Tests\TestCase;

class CourseSyncServiceTest extends TestCase
{
    public function test_to_department_code_formats_loc_prefix(): void
    {
        $this->assertSame('LOC001', CourseSyncService::toDepartmentCode(1));
        $this->assertSame('LOC022', CourseSyncService::toDepartmentCode(22));
        $this->assertSame('LOC999', CourseSyncService::toDepartmentCode(999));
    }

    public function test_generate_course_name_prefers_type_bin_address(): void
    {
        $course = new Course();
        $course->course_type = 'ＣＶＳ';
        $course->bin_type = '一日';
        $course->address = '東京都港区';
        $dept = new Department(['name' => '横浜']);

        $svc = new CourseSyncService();
        $method = new \ReflectionMethod(CourseSyncService::class, 'generateCourseName');
        $method->setAccessible(true);
        $name = $method->invoke($svc, $course, $dept);

        $this->assertSame('ＣＶＳ - 一日 - 東京都港区', $name);
    }

    public function test_generate_course_name_fallback_uses_department_name_and_code(): void
    {
        $course = new Course();
        $course->course_code = '001-001';
        $dept = new Department(['name' => '横浜第一']);

        $svc = new CourseSyncService();
        $method = new \ReflectionMethod(CourseSyncService::class, 'generateCourseName');
        $method->setAccessible(true);
        $name = $method->invoke($svc, $course, $dept);

        $this->assertSame('横浜第一 001-001', $name);
    }
}
