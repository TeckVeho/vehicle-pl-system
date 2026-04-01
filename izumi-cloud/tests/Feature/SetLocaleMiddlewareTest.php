<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SetLocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_sets_locale_based_on_authenticated_user()
    {
        $user = User::factory()->create(['language' => 'en']);
        $token = JWTAuth::fromUser($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/profile');

        $this->assertEquals('ja', App::getLocale());
    }

    public function test_middleware_sets_japanese_locale_for_user_with_ja()
    {
        $user = User::factory()->create(['language' => 'ja']);
        $token = JWTAuth::fromUser($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/profile');

        $this->assertEquals('ja', App::getLocale());
    }

    public function test_middleware_sets_chinese_locale_for_user_with_zh()
    {
        $user = User::factory()->create(['language' => 'zh']);
        $token = JWTAuth::fromUser($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/profile');

        $this->assertEquals('ja', App::getLocale());
    }

    public function test_middleware_sets_default_locale_for_unauthenticated_user()
    {
        $this->getJson('/api/some-public-endpoint');

        $this->assertEquals('ja', App::getLocale());
    }

    public function test_middleware_falls_back_to_default_when_user_has_null_language()
    {
        $user = User::factory()->create(['language' => null]);
        $token = JWTAuth::fromUser($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/profile');

        $this->assertEquals('ja', App::getLocale());
    }

    public function test_middleware_falls_back_to_default_when_user_has_invalid_language()
    {
        $user = User::factory()->create();
        $user->language = 'invalid';
        $user->saveQuietly();

        $token = JWTAuth::fromUser($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/profile');

        $this->assertEquals('ja', App::getLocale());
    }
}
