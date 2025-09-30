<?php

namespace Database\Factories;

use App\Models\User\OTP;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User\OTP>
 */
class OTPFactory extends Factory
{
    protected $model = OTP::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'login_id' => $this->faker->numerify('9#########'),
            'otp_code' => rand(111111, 999999),
            'used' => 0,
            // 'token' => Str::random(60)
        ];
    }
}
