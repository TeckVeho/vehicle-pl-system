<?php

namespace Tests\Unit;

use App\Models\System;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Repository\SystemRepository;
use Tests\TestCase;

class SystemUnitTest extends TestCase
{
    use RefreshDatabase;

    private SystemRepository $systemRepository;

    /** @var array<int, array{name: string}> */
    private array $dataSystem = [];

    private \Faker\Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->systemRepository = new SystemRepository($this->app);
        $this->faker = Faker::create();
        $this->dataSystem = $this->fakeSystemData(10, $this->faker);
    }

    public function test_assert_model(): void
    {
        $model = $this->systemRepository->model();
        $this->assertEquals(System::class, $model);
    }

    public function test_system_create_one(): void
    {
        $result = $this->systemRepository->create($this->dataSystem[0]);
        $this->assertModelExists($result);
    }

    public function test_system_list(): void
    {
        foreach ($this->dataSystem as $key => $data) {
            $this->systemRepository->create($data);
        }
        $result = $this->systemRepository->all();
        $this->assertArrayHasKey('id', $result[0], 'No ID');
        $this->assertArrayHasKey('name', $result[0], 'No Name');
    }

    /**
     * @return array<int, array{name: string}>
     */
    private function fakeSystemData(int $amount, \Faker\Generator $faker): array
    {
        $systems = [];
        for ($i = 0; $i < $amount; $i++) {
            $systems[] = [
                'name' => $faker->name(),
            ];
        }

        return $systems;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
