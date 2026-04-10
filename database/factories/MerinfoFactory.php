<?php

namespace Database\Factories;

use App\Models\Merinfo;
use App\Models\Short;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerinfoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Merinfo>
     */
    protected $model = Merinfo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'short_id' => Short::factory(),
            'name' => $this->faker->sentence(),
            'givenNameOrFirstName' => $this->faker->sentence(),
            'personalNumber' => $this->faker->word(),
            'pnr' => $this->faker->word(),
            'address' => $this->faker->address(),
            'gender' => $this->faker->word(),
            'is_celebrity' => $this->faker->boolean(),
            'has_company_engagement' => $this->faker->boolean(),
            'number_plus_count' => $this->faker->randomNumber(),
            'phone_number' => $this->faker->phoneNumber(),
            'url' => $this->faker->url(),
            'same_address_url' => $this->faker->url(),
        ];
    }
}
