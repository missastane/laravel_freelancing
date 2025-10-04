<?php

namespace Database\Factories;

use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use App\Models\Market\ProposalMilestone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->create()->id,
            'proposal_milestone_id' => ProposalMilestone::factory()->create()->id,
            'price' => fake()->numberBetween(1000,999999),
            'freelancer_amount' => 900,
            'platform_fee' => 100

        ];
    }
}
