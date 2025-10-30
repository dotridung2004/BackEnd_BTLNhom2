<?php

namespace Database\Factories;

use App\Models\Department; // Cần import Department
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Học phần ' . $this->faker->sentence(3), // Ví dụ: "Học phần Kỹ thuật phần mềm"
            'code' => strtoupper($this->faker->unique()->bothify('??###')), // Ví dụ: IT101
            'credits' => $this->faker->randomElement([2, 3, 4]),
            'department_id' => Department::factory(), // Tự động tạo hoặc lấy 1 Department
        ];
    }
}