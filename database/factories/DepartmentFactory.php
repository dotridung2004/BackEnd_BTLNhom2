<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Khoa ' . $this->faker->sentence(2), // Ví dụ: "Khoa Công nghệ mới"
            'head_id' => null, // Sẽ cập nhật sau
        ];
    }
}