<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => 1,
            'name' => fake()->company(),
            'address' => fake()->address(),
            'location' => fake()->latitude() . ',' . fake()->longitude(),
            'token' => fake()->unique()->lexify('branch_??????'),
            'region_id' => Region::factory(),
            'phones' => json_encode([fake()->phoneNumber()]),
            'link' => fake()->url(),
            'info' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the branch is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
