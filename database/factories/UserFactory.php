<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
            'phone'             => $this->faker->phoneNumber(),
            'bio'               => $this->faker->paragraph(3),
            'address'           => $this->faker->streetAddress(),
            'city'              => $this->faker->city(),
            'state'             => $this->faker->state(),
            'zip_code'          => $this->faker->postcode(),
            'country'           => $this->faker->country(),
            'is_approved'       => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is pending approval.
     */
    public function pendingApproval(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_approved' => false,
        ]);
    }

    /**
     * Create a super admin user.
     */
    public function superAdmin(): static
    {
        return $this->state(fn(array $attributes) => [
            'name'  => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'name'  => 'Admin User',
            'email' => 'admin@example.com',
        ]);
    }

    /**
     * Create a volunteer user.
     */
    public function volunteer(): static
    {
        return $this->state(fn(array $attributes) => [
            'name'  => 'Volunteer User',
            'email' => 'volunteer@example.com',
        ]);
    }
}
