<?php

namespace Database\Factories;

use App\Models\Ticket\TicketDepartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket\TicketDepartment>
 */
class TicketDepartmentFactory extends Factory
{
    protected $model = TicketDepartment::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name()
        ];
    }
}
