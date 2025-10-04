<?php

namespace Database\Factories;

use App\Models\Market\OrderItem;
use App\Models\User\DisputeRequest;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User\DisputeRequest>
 */
class DisputeRequestFactory extends Factory
{
    protected $model = DisputeRequest::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory()->create()->id,
            'raised_by' => User::factory()->employer()->create()->id,
            'status' => 2, // not open
            'reason' => 'initial lock',
        ];
    }
}
