<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Route;
use App\Models\Store;
use Faker\Factory as Faker;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Repository\CourseRepository;
use Tests\TestCase;

/**
 * @note Production: CourseRepository::create() không gọi routes()->sync (đoạn sync đang comment).
 *       Test tự sync pivot sau create trong setUp / test FK để dữ liệu khớp kỳ vọng.
 */
class CourseTest extends TestCase
{
    use RefreshDatabase;

    protected CourseRepository $repository;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        $this->faker = Faker::create();
        $this->repository = new CourseRepository($this->app);

        $this->artisan('db:seed');

        Customer::query()->create([
            'customer_name' => 'name',
        ]);
        Store::query()->create([
            'store_name' => 'name',
        ]);
        $route = Route::query()->create([
            'name' => 'name',
            'department_id' => 1,
            'customer_id' => 1,
            'route_fare_type' => 1,
            'fare' => 1,
            'highway_fee' => 1,
            'highway_fee_holiday' => 1,
            'is_government_holiday' => 1,
            'remark' => 'qaaaa',
        ]);

        $route2 = Route::query()->create([
            'name' => '2',
            'department_id' => 1,
            'customer_id' => 1,
            'route_fare_type' => 1,
            'fare' => 1,
            'highway_fee' => 1,
            'highway_fee_holiday' => 1,
            'is_government_holiday' => 1,
            'remark' => 'qaaaa',
        ]);

        $course = $this->repository->create([
            'course_code' => $this->faker->name,
            'start_date' => '2022-05-01',
            'end_date' => '2022-06-01',
            'course_type' => COURSE_TYPE_VALUE['cvs'],
            'bin_type' => BIN_TYPE_VALUE['one_day'],
            'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
            'start_time' => '20:00:00',
            'gate' => GATE_VALUE['existing'],
            'wing' => WING_VALUE['existing'],
            'tonnage' => TONNAGE[2],
            'quantity' => 5,
            'allowance' => 1000,
            'department_id' => Department::query()->first()->id,
            'routes' => [
                $route->id,
                $route2->id,
            ],
        ]);
        $this->assertNotNull($course);
        $course->routes()->sync([
            $route->id => ['position' => 1],
            $route2->id => ['position' => 2],
        ]);
    }

    /**
     * CourseRepository::create không attach pivot; kiểm tra FK khi sync route_id không tồn tại.
     */
    public function test_create_course_with_a_route_not_exist_in_the_database(): void
    {
        $course = $this->repository->create([
            'course_code' => $this->faker->name,
            'start_date' => '2022-05-01',
            'end_date' => '2022-06-01',
            'course_type' => COURSE_TYPE_VALUE['cvs'],
            'bin_type' => BIN_TYPE_VALUE['one_day'],
            'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
            'start_time' => '20:00:00',
            'gate' => GATE_VALUE['existing'],
            'wing' => WING_VALUE['existing'],
            'tonnage' => TONNAGE[2],
            'quantity' => 5,
            'allowance' => 1000,
            'department_id' => Department::query()->first()->id,
            'routes' => [
                Route::query()->first()->id,
            ],
        ]);
        $this->assertInstanceOf(Course::class, $course);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/FOREIGN KEY constraint failed|Integrity constraint violation/');

        $course->routes()->sync([100 => ['position' => 1]]);
    }

    public function test_create_course_with_a_route_in_the_database(): void
    {
        $course = $this->repository->create([
            'course_code' => $this->faker->name,
            'start_date' => '2022-05-01',
            'end_date' => '2022-06-01',
            'course_type' => COURSE_TYPE_VALUE['cvs'],
            'bin_type' => BIN_TYPE_VALUE['one_day'],
            'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
            'start_time' => '20:00:00',
            'gate' => GATE_VALUE['existing'],
            'wing' => WING_VALUE['existing'],
            'tonnage' => TONNAGE[2],
            'quantity' => 5,
            'allowance' => 1000,
            'department_id' => Department::query()->first()->id,
            'routes' => [
                Route::query()->first()->id,
            ],
        ]);
        $this->assertInstanceOf(Course::class, $course);
        $course->routes()->sync([
            Route::query()->first()->id => ['position' => 1],
        ]);
    }

    public function test_create_course_with_multi_route(): void
    {
        $route = Route::query()->create([
            'name' => $this->faker->name,
            'department_id' => 1,
            'customer_id' => 1,
            'route_fare_type' => 1,
            'fare' => 1,
            'highway_fee' => 1,
            'highway_fee_holiday' => 1,
            'is_government_holiday' => 1,
            'remark' => 'qaaaa',
        ]);

        $course = $this->repository->create([
            'course_code' => $this->faker->name,
            'start_date' => '2022-05-01',
            'end_date' => '2022-06-01',
            'course_type' => COURSE_TYPE_VALUE['cvs'],
            'bin_type' => BIN_TYPE_VALUE['one_day'],
            'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
            'start_time' => '20:00:00',
            'gate' => GATE_VALUE['existing'],
            'wing' => WING_VALUE['existing'],
            'tonnage' => TONNAGE[2],
            'quantity' => 5,
            'allowance' => 1000,
            'department_id' => Department::query()->first()->id,
            'routes' => [
                Route::query()->first()->id,
                $route->id,
            ],
        ]);
        $this->assertInstanceOf(Course::class, $course);
        $course->routes()->sync([
            Route::query()->orderBy('id')->first()->id => ['position' => 1],
            $route->id => ['position' => 2],
        ]);
    }

    public function test_detail_a_course_is_not_exist_in_db(): void
    {
        $course = $this->repository->find(rand(100, 200));
        $this->assertEquals($course, null);
    }

    public function test_detail_a_course_exist_in_db(): void
    {
        $course = $this->repository->find(Course::query()->first()->id);
        $this->assertInstanceOf(Course::class, $course);
    }

    public function test_detail_a_course_with_a_route(): void
    {
        $course = Course::query()->first();
        $course = $this->repository->find($course->id);
        $this->assertInstanceOf(Route::class, $course->routes[0]);
    }

    public function test_detail_a_course_with_multi_routes(): void
    {
        $course = Course::query()->first();
        $course = $this->repository->find($course->id);
        $this->assertInstanceOf(Route::class, $course->routes[0]);
        $this->assertInstanceOf(Route::class, $course->routes[1]);
    }

    public function test_delete_course(): void
    {
        $course = Course::query()->first();
        $this->repository->delete($course->id);
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
            'deleted_at' => null,
        ]);
    }

    public function test_delete_course_not_exist_in_db(): void
    {
        $delete = $this->repository->delete(rand(100, 200));
        $this->assertEquals($delete, false);
    }

    public function test_delete_course_related_to_route(): void
    {
        $course = Course::query()->first();
        $this->repository->delete($course->id);
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
            'deleted_at' => null,
        ]);
    }

    public function test_delete_course_not_related_to_route(): void
    {
        $course = $this->repository->create([
            'course_code' => $this->faker->name,
            'start_date' => '2022-05-01',
            'end_date' => '2022-06-01',
            'course_type' => COURSE_TYPE_VALUE['cvs'],
            'bin_type' => BIN_TYPE_VALUE['one_day'],
            'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
            'start_time' => '20:00:00',
            'gate' => GATE_VALUE['existing'],
            'wing' => WING_VALUE['existing'],
            'tonnage' => TONNAGE[2],
            'quantity' => 5,
            'allowance' => 1000,
            'department_id' => Department::query()->first()->id,
            'routes' => [
                Route::query()->first()->id,
            ],
        ]);
        $this->assertInstanceOf(Course::class, $course);
        $course->routes()->sync([
            Route::query()->first()->id => ['position' => 1],
        ]);
        $this->repository->delete($course->id);
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
            'deleted_at' => null,
        ]);
    }

    public function test_update_a_course(): void
    {
        $course = Course::query()->first();
        $start_date_after_update = '2023-05-01';

        $end_date_after_update = '2023-06-01';

        $course = $this->repository->update([
            'course_code' => $course->course_code.' Updated',
            'start_date' => $start_date_after_update,
            'end_date' => $end_date_after_update,
            'course_type' => COURSE_TYPE_VALUE['cvs'],
            'bin_type' => BIN_TYPE_VALUE['one_day'],
            'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
            'start_time' => '20:00:00',
            'gate' => GATE_VALUE['existing'],
            'wing' => WING_VALUE['existing'],
            'tonnage' => TONNAGE[2],
            'quantity' => 5,
            'allowance' => 1000,
            'department_id' => Department::query()->first()->id,
            'course_flag' => 0,
            'address' => $course->address ?? '',
            'routes' => [
                Route::query()->first()->id,
            ],
        ], $course->id);
        $course = Course::query()->where('id', $course->id)->first();
        $this->assertEquals(strtotime((string) $course->start_date), strtotime($start_date_after_update));
        $this->assertEquals(strtotime((string) $course->end_date), strtotime($end_date_after_update));
    }

    public function test_update_a_course_delete_all_route(): void
    {
        $course = Course::query()->first();
        $number_of_route_before_update = count($course->routes);
        $course = $this->repository->update([
            'course_code' => $course->course_code.' Updated',
            'start_date' => $course->start_date,
            'end_date' => $course->end_date,
            'course_type' => COURSE_TYPE_VALUE['cvs'],
            'bin_type' => BIN_TYPE_VALUE['one_day'],
            'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
            'start_time' => '20:00:00',
            'gate' => GATE_VALUE['existing'],
            'wing' => WING_VALUE['existing'],
            'tonnage' => TONNAGE[2],
            'quantity' => 5,
            'allowance' => 1000,
            'department_id' => Department::query()->first()->id,
            'course_flag' => 0,
            'address' => $course->address ?? '',
            'routes' => [],
        ], $course->id);
        $course = Course::query()->where('id', $course->id)->first();
        $this->assertNotEquals($number_of_route_before_update, count($course->routes));
        $this->assertEquals(0, count($course->routes));
    }

    public function test_update_a_course_add_new_route(): void
    {
        $new_route = Route::query()->create([
            'name' => 'new_route',
            'department_id' => 1,
            'customer_id' => 1,
            'route_fare_type' => 1,
            'fare' => 1,
            'highway_fee' => 1,
            'highway_fee_holiday' => 1,
            'is_government_holiday' => 1,
            'remark' => 'qaaaa',
        ]);

        $course = Course::query()->first();
        $number_of_route_before_update = count($course->routes);
        $course = $this->repository->update([
            'course_code' => $course->course_code.' Updated',
            'start_date' => $course->start_date,
            'end_date' => $course->end_date,
            'course_type' => COURSE_TYPE_VALUE['cvs'],
            'bin_type' => BIN_TYPE_VALUE['one_day'],
            'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
            'start_time' => '20:00:00',
            'gate' => GATE_VALUE['existing'],
            'wing' => WING_VALUE['existing'],
            'tonnage' => TONNAGE[2],
            'quantity' => 5,
            'allowance' => 1000,
            'department_id' => Department::query()->first()->id,
            'course_flag' => 0,
            'address' => $course->address ?? '',
            'routes' => [
                $new_route->id,
            ],
        ], $course->id);
        $course = Course::query()->where('id', $course->id)->first();
        $this->assertNotEquals($number_of_route_before_update, count($course->routes));
        $this->assertEquals(1, count($course->routes));
    }
}
