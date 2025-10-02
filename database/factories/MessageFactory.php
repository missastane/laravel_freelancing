<?php

namespace Database\Factories;

use App\Models\Market\Conversation;
use App\Models\Market\Message;
use App\Models\Market\Order;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $employer = User::factory()->employer()->create();
        $freelancer = User::factory()->freelancer()->create();
        return [
            'conversation_id' => Conversation::factory()->create([
                'employee_id' => $freelancer->id,
                'employer_id' => $employer->id
            ])->id,
            'sender_id' => $freelancer->id,
            'message_context' => Order::class,
            'message_context_id' => Order::factory()->create([
                'freelancer_id' => $freelancer->id,
                'employer_id' => $employer->id,
            ]),
            'message' => fake()->text(),
            'sent_date' => now()
        ];
    }
}
