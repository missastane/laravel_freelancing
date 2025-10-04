<?php

namespace Database\Factories;

use App\Models\Market\Proposal;
use App\Models\Market\ProposalMilestone;
use App\Models\User\User;
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
            'duration_time' => fake()->numberBetween(1,360),
            'proposal_id' => Proposal::factory()->create()->id,
           
        ];
    }
}
