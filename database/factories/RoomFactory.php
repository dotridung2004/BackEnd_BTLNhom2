<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Phòng ' . $this->faker->bothify('?###'), // Ví dụ: "Phòng A101"
            'capacity' => $this->faker->randomElement([30, 50, 70, 100]),
            'location' => 'Tòa ' . $this->faker->randomElement(['A', 'B', 'C', 'H']),
        ];
    }
}