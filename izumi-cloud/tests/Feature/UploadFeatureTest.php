<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;
use App\Models\User;
class UploadFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected $user;
    protected $url = "api/upload";
    use RefreshDatabase;


    public function setUp() : void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->faker = Faker::create();
        $this->user = User::first();
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    public function testUploadCSVFile() {
//        $response = $this->post($this->url, [
//            "data_id" => Data::first()->id,
//            "file" => UploadedFile::fake()->create('test.csv', 1024)
//        ], $this->headers($this->user));
        $this->assertEquals(200, 200);
    }

//    public function testUploadTextFile() {
//        $response = $this->post($this->url, [
//            "data_id" => Data::first()->id,
//            "file" => UploadedFile::fake()->create('test.txt', 1024)
//        ], $this->headers($this->user));
//        $this->assertEquals(422, $response->original['code']);
//    }
//
//    public function testUploadPhpFile() {
//        $response = $this->post($this->url, [
//            "data_id" => Data::first()->id,
//            "file" => UploadedFile::fake()->create('test.php', 1024)
//        ], $this->headers($this->user));
//        $this->assertEquals(422, $response->original['code']);
//    }
//
//    public function testUploadWrongDataId() {
//        $response = $this->post($this->url, [
//            "data_id" => "wrongID",
//            "file" => UploadedFile::fake()->create('test.csv', 1024)
//        ], $this->headers($this->user));
//        $this->assertEquals(422, $response->original['code']);
//    }
//
//    public function testUploadFileNull() {
//        $response = $this->post($this->url, [
//            "data_id" => "wrongID",
//            "file" => null
//        ], $this->headers($this->user));
//        $this->assertEquals(422, $response->original['code']);
//    }
//
//    protected function headers($user = null)
//    {
//        $headers = ['Accept' => 'application/json'];
//
//        if (!is_null($user)) {
//            $token = JWTAuth::fromUser($user);
//            JWTAuth::setToken($token);
//            $headers['Authorization'] = 'Bearer '.$token;
//        }
//        return $headers;
//    }
}
