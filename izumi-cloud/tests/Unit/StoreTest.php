<?php

namespace Tests\Unit;

use App\Models\Store;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Repository\StoreRepository;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected StoreRepository $storeRepository;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->storeRepository = new StoreRepository($this->app);
        $this->faker = Faker::create();
    }

    public function test_create_a_new_store()
    {
        $result = $this->storeRepository->create([
            'store_name' => $this->faker->name.' this is a fake name of the store',
        ]);
        $this->assertInstanceOf(Store::class, $result);
    }

    public function test_update_a_currnet_store()
    {
        $result = $this->storeRepository->create([
            'store_name' => $this->faker->name.' this is a fake name of the store',
        ]);
        $newName = $this->faker->name.' this is a fake name of the store - update';
        $firstModel = $this->storeRepository->find($this->storeRepository->first()->id);
        $result = $this->storeRepository->update([
            'store_name' => $newName,
        ], $firstModel->id);
        $this->assertInstanceOf(Store::class, $result);
        $this->assertEquals($result->store_name, $newName);
    }

    public function test_update_a_currnet_store_not_exist()
    {
        $this->storeRepository->create([
            'store_name' => $this->faker->name.' this is a fake name of the store',
        ]);
        $this->expectException(ModelNotFoundException::class);
        $newName = $this->faker->name.' this is a fake name of the store - update';
        $this->storeRepository->update([
            'store_name' => $newName,
        ], 100);
    }

    public function test_get_detail_a_store()
    {
        $result = $this->storeRepository->create([
            'store_name' => $this->faker->name.' this is a fake name of the store',
        ]);
        $result = $this->storeRepository->find($this->storeRepository->first()->id);
        $this->assertInstanceOf(Store::class, $result);
    }

    public function test_get_detail_a_store_not_exist()
    {
        $this->storeRepository->create([
            'store_name' => $this->faker->name.' this is a fake name of the store',
        ]);
        $this->expectException(ModelNotFoundException::class);
        $this->storeRepository->find(100);
    }

    public function test_delete_a_current_store()
    {
        $result = $this->storeRepository->create([
            'store_name' => $this->faker->name.' this is a fake name of the store',
        ]);
        $currentStore = $this->storeRepository->find($this->storeRepository->first()->id);
        $result = $this->storeRepository->delete($currentStore->id);
        $this->assertDatabaseMissing('stores', [
            'id' => $currentStore->id,
            'deleted_at' => null,
        ]);
    }

    public function test_delete_a_store_not_exist()
    {
        $this->storeRepository->create([
            'store_name' => $this->faker->name.' this is a fake name of the store',
        ]);
        $this->expectException(ModelNotFoundException::class);
        $this->storeRepository->delete(100);
    }
}
