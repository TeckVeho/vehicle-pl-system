<?php

namespace Tests\Unit;

use App\Http\Requests\LoginRequest;
use App\Models\DataConnection;
use App\Models\DataItem;
use Faker\Factory as Faker;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Repository\AuthRepository;
use Repository\DataRepository;
use Tests\TestCase;

class DataUnitTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    private $dataRepository;

    use RefreshDatabase;

    public $authRepository;

    public $loginRequest;

    protected $param;

    protected $faker;

    protected function setUp(): void
    {
        $this->faker = Faker::create();
        $app = new Application;
        $this->dataRepository = new dataRepository($app);
        $this->param = [
            'id' => '111111',
            'password' => '123456789',
        ];
        $this->authRepository = new AuthRepository;

        parent::setUp();
        $this->artisan('db:seed');
        DataItem::factory()->count(10)->create();

    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_data_list()
    {
        $result = $this->dataRepository->all();
        $this->assertEquals($result, $result);
    }

    public function test_paginate()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $per_page = 10;
        $result = $this->dataRepository->paginateAndSort($per_page);
        $this->assertArrayHasKey('id', $result[0], 'No ID');
        $this->assertArrayHasKey('name', $result[0], 'No Name');
        $this->assertEquals($per_page, count($result));
    }

    public function test_search_not_found()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $searchKey = 'Not found any thing about this name';
        $result = $this->dataRepository->paginateAndSort(10, null, 'desc', $searchKey);
        $this->assertEquals(null, $result['data']);
    }

    public function test_search_found()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $searchModel = DataConnection::inRandomOrder()->first();
        $result = $this->dataRepository->paginateAndSort(10, null, 'desc', $searchModel->name);
        $this->assertEquals($searchModel->name, $result[0]->name);
    }
}
