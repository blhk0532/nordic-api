<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Database\Factories;

use CybertronianKelvin\Graper\Models\GraperPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class GraperPageFactory extends Factory
{
    protected $model = GraperPage::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'html' => null,
            'css' => null,
            'project_data' => null,
            'css_class' => null,
            'is_published' => false,
            'created_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true]);
    }

    public function withContent(): static
    {
        return $this->state([
            'html' => '<div>Hello world</div>',
            'css' => 'div { color: red; }',
            'project_data' => ['pages' => [['frames' => []]]],
        ]);
    }
}
