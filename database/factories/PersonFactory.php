<?php

namespace Database\Factories;

use App\Models\Merinfo;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Person>
     */
    protected $model = Person::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'adress' => $this->faker->word(),
            'zip' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'kommun' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'ratsit' => $this->faker->word(),
            'hitta' => $this->faker->word(),
            'merinfo' => $this->faker->word(),
            'gender' => $this->faker->word(),
            'person' => $this->faker->word(),
            'street' => $this->faker->word(),
            'merinfo_id' => Merinfo::factory(),
            'merinfo_phone' => $this->faker->phoneNumber(),
        ];
    }
}
