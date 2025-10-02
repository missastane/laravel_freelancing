<?php

namespace Database\Factories;

use App\Models\Market\ProposalMilestone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\ProposalMilestone>
 */
class ProposalMilestoneFactory extends Factory
{
    protected $model = ProposalMilestone::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'description' => fake()->text(),
            'amount' => fake()->numberBetween(1000,10000000),
            'duration_time' => fake()->numberBetween(1,360)
        ];
    }
}
