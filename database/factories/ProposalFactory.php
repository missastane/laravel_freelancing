<?php

namespace Database\Factories;

use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\Proposal>
 */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory()->create()->id,
            'description' => 'پیشنهاد برای پروژه فلان',
        ];
    }
}
