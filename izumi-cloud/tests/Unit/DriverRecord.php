<?php

namespace Tests\Unit;

use App\Models\DriverRecorder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Pagination\LengthAwarePaginator;
use Repository\DriverRecorderRepository;
class DriverRecord extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */

    protected $repository;
    use RefreshDatabase;

    public function setUp(): void
    {
        $this->faker = Faker::create();
        parent::setUp();
        $this->artisan('db:seed');
        $app = new Application();
        $this->customeData();
        $this->repository = new DriverRecorderRepository($app);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testDriverRecordPaginate() {
       $data = $this->repository->paginate();
       $this->assertInstanceOf(LengthAwarePaginator::class, $data);
    }

    public function testDriverRecordPaginateNextPage() {
        $per_page = 100;
        $data = $this->repository->paginate($per_page);
        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
    }

    public function testFilterByDepartmentId() {
        $per_page = 100;
        $departmentFilter = 1;
        $data = $this->repository->paginate($per_page, [
            "department_id" => $departmentFilter
        ]);
        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $rs = $data->items();
        $this->assertEquals(1, count($rs));
        $this->assertEquals($rs[0]->department_id, $departmentFilter);
    }

    public function testFilterByMonth() {
        $per_page = 100;
        $month = "2022-11-10";
        $data = $this->repository->paginate($per_page, [
            "month" => $month
        ]);
        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $rs = $data->items();
        $this->assertEquals(1, count($rs));
        $this->assertEquals($rs[0]->record_date, $month);
    }

    public function testFilterByType() {
        $per_page = 100;
        $typeFilter = 2;
        $data = $this->repository->paginate($per_page, [
            "type" => $typeFilter
        ]);
        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $rs = $data->items();
        $this->assertEquals(1, count($rs));
        $this->assertEquals($rs[0]->type, $typeFilter);
    }

    private function customeData() {
        DriverRecorder::create([
            'department_id' => 1,
            'record_date' => '2022-10-10',
            'title' => 'testFilterByDepartmentId',
            'type' => 1
        ]);

        DriverRecorder::create([
            'department_id' => 2,
            'record_date' => '2022-11-10',
            'title' => 'testFilterByMonth',
            'type' => 1
        ]);

        DriverRecorder::create([
            'department_id' => 3,
            'record_date' => '2022-12-10',
            'title' => 'testFilterByType',
            'type' => 2
        ]);
    }
}
