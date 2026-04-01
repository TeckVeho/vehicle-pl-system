<?php

namespace Tests\Unit;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResetPwTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->artisan('db:seed');

        $this->user = User::create([
            'name' => $this->faker->name,
            'id' => 123456,
            'email' => 'test@gmail.com',
            'password' => '123456789',
            'role' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * A basic unit test example 1.
     *
     * @return void
     */
    #[Test]
    public function test_reset_pass_word_successfully()
    {
        $response = $this->call('POST', 'api/remind-passwords', [
            'emp_code' => '123456',
            '_token' => csrf_token(),
        ]);
        $this->assertEquals(true, $response->getStatusCode(), 'Submit request successfully');

    }

    public function test_reset_not_have_password()
    {
        $response = $this->postJson('api/remind-passwords', [
            'emp_code' => '',
            '_token' => csrf_token(),
        ]);
        $response->assertJson(['code' => '401']);
        $response->assertJson(['message' => '入力した社員番号は存在しません']);
    }

    public function test_reset_pass_word_wrong_code()
    {
        $response = $this->postJson('api/remind-passwords', [
            'emp_code' => '12345',
            '_token' => csrf_token(),
        ]);
        $response->assertJson(['code' => '401']);
        $response->assertJson(['message' => '入力した社員番号は存在しません']);
    }
}
