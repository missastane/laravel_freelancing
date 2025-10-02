<?php

namespace Database\Factories;

use App\Models\Market\File;
use App\Models\Market\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Market\File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $message = Message::factory()->create();
        return [
            'filable_type' => Message::class,
            'filable_id' => $message->id,
            'file_name' => fake()->name(),
            'file_path' => fake()->filePath(),
            'file_size' => fake()->randomNumber(),
            'file_type' => fake()->fileExtension(),
            'mime_type' => 'image/jpg',
            'uploaded_by' => $message->sender_id
        ];
    }
}
