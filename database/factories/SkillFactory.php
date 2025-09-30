<?php

namespace Database\Factories;

use App\Models\Market\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'persian_title' => 'ترجمه',
            'original_title' => 'translate'
        ];
    }
}
