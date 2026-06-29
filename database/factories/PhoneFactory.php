<?php

namespace Database\Factories;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Phone>
 */
class PhoneFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->randomElement(['Mobile', 'Office', 'WhatsApp']),
            'country_code' => '+965',
            'number' => fake()->randomNumber(8),
            'extension' => '',
            // 'notes' => fake()->sentence(),
        ];
    }
}
