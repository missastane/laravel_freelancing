<?php

namespace Database\Factories;

use App\Models\Market\Order;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $freelancer = User::factory()->freelancer()->create();
        $employer = User::factory()->employer()->create();
        return [
            'proposal_id' => Proposal::factory()->create(['freelancer_id' => $freelancer->id])->id,
            'project_id' => Project::factory()->create(['user_id' => $employer->id])->id,
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'total_price' => 100000
        ];
    }
}
