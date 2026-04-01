<?php

namespace Tests\Feature;

use App\Jobs\SyncUserJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_user_can_update_language()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/user/language', [
            'language' => 'en',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Language updated successfully',
                'data' => [
                    'language' => 'en',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'language' => 'en',
        ]);

        Queue::assertPushed(SyncUserJob::class);
    }

    public function test_user_can_update_language_to_japanese()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/user/language', [
            'language' => 'ja',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.language', 'ja');

        Queue::assertPushed(SyncUserJob::class);
    }

    public function test_user_can_update_language_to_chinese()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/user/language', [
            'language' => 'zh',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.language', 'zh');

        Queue::assertPushed(SyncUserJob::class);
    }

    public function test_update_language_rejects_invalid_language_code()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/user/language', [
            'language' => 'fr',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid language code',
            ]);

        Queue::assertNotPushed(SyncUserJob::class);
    }

    public function test_update_language_rejects_empty_language()
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/user/language', [
            'language' => '',
        ]);

        $response->assertStatus(422);

        Queue::assertNotPushed(SyncUserJob::class);
    }

    public function test_update_language_requires_authentication()
    {
        Queue::fake();

        $response = $this->postJson('/api/user/language', [
            'language' => 'en',
        ]);

        $response->assertStatus(401);

        Queue::assertNotPushed(SyncUserJob::class);
    }

    public function test_sync_user_job_dispatched_with_correct_user_id()
    {
        Queue::fake();

        $this->user->update(['language' => 'en']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/user/language', [
            'language' => 'zh',
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(SyncUserJob::class, 1);
    }
}
