<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     */

    public function test_can_login()
    {
        $user = User::factory()->create(['password' => '123456', 'phone' => '998990000000']);

        $response = $this->post('/api/auth/login', ['phone' => $user->phone, 'password' => '123456']);
        $response->assertStatus(200);
    }

    public function test_can_get_me(): void
    {
        $user = User::query()->where('is_admin', 1)->first();
        $this->actingAs($user);

        $response = $this->get('/api/auth/me');
        $response->assertStatus(200);
    }
}
