<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Product\ProductCategory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
        $branch = Branch::factory()->create();
        $user = User::query()->where('is_admin', 1)->first();
        $cat = ProductCategory::query()->first();

        $this->actingAs($user);

        $response = $this->post('/api/shelf/add', [
            'branch_id' => $branch->id,
            'category_sku' => $cat->sku,
            'is_paddon' => 0,
            'type' => 6,
            'items' => [
                [
                    'product_count' => 2,
                    'status_zone' => 'gold',
                    'type' => 1,
                ]
            ],
        ]);

        $response->assertStatus(200);
    }

    public function test_can_add_shelf_v2()
    {
        $branch = Branch::factory()->create();
        $user = User::query()->where('is_admin', 1)->first();
        $cat = ProductCategory::query()->first();

        $this->actingAs($user);

        $response = $this->post('/api/shelf/add/v2', [
            'branch_id' => $branch->id,
            'category_sku' => $cat->sku,
            'is_paddon' => 0,
            'type' => 6,
            'items' => [
                [
                    'product_count' => 2,
                    'status_zone' => 'gold',
                    'type' => 1,
                ]
            ],
        ]);

        $response->assertStatus(200);
    }
}
