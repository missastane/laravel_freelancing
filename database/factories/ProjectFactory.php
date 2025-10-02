<?php

namespace Database\Factories;

use App\Models\Market\Project;
use App\Models\Market\ProjectCategory;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = ProjectCategory::factory()->create();
        $user = User::factory()->employer()->create();
        return [
            'project_category_id' => $category->id,
            'user_id' => User::factory()->employer()->create()->id,
            'title' => fake()->title(),
            'description' => fake()->text(),
            'duration_time' => 5,
            'amount' => 1000000,
        ];
    }
}
