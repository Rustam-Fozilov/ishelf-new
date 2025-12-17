<?php

namespace Tests\Feature;

use App\Models\PrintLog\PrintLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShelfTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     */
    public function test_can_get_shelves_list(): void
    {
        $user = User::query()->where('is_admin', 1)->first();
        $this->actingAs($user);

        $response = $this->get('/api/shelf/list', ['per_page' => 1]);
        $response->assertStatus(200);
    }

    public function test_can_add_shelf()
    {
        //
    }
}
