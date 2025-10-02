<?php

namespace Database\Factories;

use App\Models\Market\Conversation;
use App\Models\Market\Order;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->freelancer()->create();
        $employer = User::factory()->employer()->create();

        return [
            'employee_id' => $user->id,
            'employer_id' => $employer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => Order::factory()->create([
                'freelancer_id' => $user->id,
                'employer_id' => $employer->id,
            ])->id
        ];
    }
}
