<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['male', 'female']);
        $firstName = $gender === 'male' ? $this->faker->firstNameMale() : $this->faker->firstNameFemale();
        $lastName = $this->faker->lastName();
        
        return [
            'name' => $lastName . ' ' . $firstName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), // Mật khẩu mặc định là 'password'
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'gender' => $gender,
            'date_of_birth' => $this->faker->date(),
            'role' => 'student', // Mặc định là student
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    // Định nghĩa một "state" (trạng thái) cho Teacher
    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'teacher',
        ]);
    }

    // Định nghĩa state cho Trưởng khoa
    public function headOfDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'head_of_department',
        ]);
    }
    
    // Định nghĩa state cho Giáo vụ
    public function trainingOffice(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'training_office',
        ]);
    }
}