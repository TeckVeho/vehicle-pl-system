<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\DataConnectionController;
use App\Http\Requests\DataConnectionRequest;
use App\Http\Requests\LoginRequest;
use App\Models\DataConnection;
use App\Repositories\Contracts\DataConnectionRepositoryInterface;
use Faker\Factory as Faker;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery as m;
use Repository\AuthRepository;
use Repository\DataConnectionRepository;
use Tests\TestCase;

class DataConnectionListTest extends TestCase
{
    protected $DataConnection;

    protected $DataConnectionRepository;

    protected $param;

    /**
     * @var DataConnectionController
     */
    protected $DataConnectionController;

    protected $repository;

    protected $DataConnectionRequest;

    public $authRepository;

    public $loginRequest;

    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        $app = new Application;
        $dataRepository = new DataConnectionRepository($app);

        $this->afterApplicationCreated(function () use ($dataRepository) {
            $this->DataConnectionRepository = m::mock($dataRepository)->makePartial();
            $this->DataConnectionController = new DataConnectionController(
                $this->app->instance(DataConnectionRepositoryInterface::class, $this->DataConnectionRepository)
            );
        });
        $this->DataConnectionRequest = new DataConnectionRequest;
        $this->faker = Faker::create();

        // chuẩn bị dữ liệu test
        $this->DataConnection = [
            'name' => $this->faker->name(),
            'type' => $this->faker->name(),
            'system_from_id' => rand(1, 1000),
            'system_to_id' => rand(1, 1000),
            'frequency' => '1.11',
            'cron_time' => '16:26:46',
            'cron_date' => '2021-09-28',
            'status_final' => $this->faker->name(),
            'data_connection_id' => rand(1, 1000),
        ];
        $this->param = [
            'id' => '111111',
            'password' => '123456789',
        ];
        $this->authRepository = new AuthRepository;
        $this->loginRequest = new LoginRequest;

        parent::setUp();
        $this->artisan('db:seed');

    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * test User.
     *
     * @param  DataConnectionRequest  $this  - >param
     * @param  null  $guard
     * @return void
     */
    //  Test OK
    public function test_data_connection_show_id()
    {
        // Gọi hàm tạo
        $dataConnection = DataConnection::first();
        $response = $this->DataConnectionController->show($dataConnection->id);
        $this->assertEquals(200, $response->getStatusCode());
    }

    //    Test OK
    public function test_data_connection_show_all()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $this->DataConnectionRequest->merge([]);
        $response = $this->DataConnectionController->index($this->DataConnectionRequest);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_with_paginate()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $this->DataConnectionRequest->merge([
            'per_page' => 10,
        ]);
        $response = $this->DataConnectionController->index($this->DataConnectionRequest);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_not_found()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $this->DataConnectionRequest->merge([
            'name' => 'test search not found',
        ]);
        $response = $this->DataConnectionController->index($this->DataConnectionRequest);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_success()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $this->DataConnectionRequest->merge(['name' => 'test']);
        $response = $this->DataConnectionController->index($this->DataConnectionRequest);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_send_data_to_maintenance()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $this->DataConnectionRequest->merge(['id' => '15']);
        $response = $this->DataConnectionController->index($this->DataConnectionRequest);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_get_data_to_maintenance_cost()
    {
        $request = new LoginRequest;
        $request->merge($this->param);
        $this->authRepository->doLogin($request, $guard = null);
        $this->DataConnectionRequest->merge(['id' => '16']);
        $response = $this->DataConnectionController->index($this->DataConnectionRequest);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
