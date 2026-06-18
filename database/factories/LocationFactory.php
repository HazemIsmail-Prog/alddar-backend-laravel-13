<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
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
            'label' => fake()->randomElement(['المنزل', 'المكتب']),
            'country' => 'الكويت',
            'city' => fake()->randomElement(['العاصمة', 'الفروانية', 'الأحمدي', 'الجهراء', 'مبارك الكبير', 'حولي']),
            'area' => fake()->randomElement(['السالمية', 'حولي', 'الفروانية']),
            'block' => fake()->numberBetween(1, 10),
            'street' => fake()->streetName(),
            'avenue' => fake()->numberBetween(1, 10),
            'building' => fake()->buildingNumber(),
            'floor' => fake()->numberBetween(1, 10),
            'notes' => fake()->sentence(),
        ];
    }
}
