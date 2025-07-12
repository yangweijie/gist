<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tags = [
            'php', 'javascript', 'python', 'java', 'css', 'html', 'sql', 'bash',
            'laravel', 'vue', 'react', 'angular', 'nodejs', 'express', 'django',
            'spring', 'bootstrap', 'tailwind', 'mysql', 'postgresql', 'mongodb',
            'redis', 'docker', 'kubernetes', 'aws', 'azure', 'gcp', 'git',
            'github', 'gitlab', 'ci-cd', 'testing', 'api', 'rest', 'graphql',
            'microservices', 'frontend', 'backend', 'fullstack', 'mobile',
            'web', 'desktop', 'game', 'ai', 'ml', 'data-science', 'blockchain'
        ];

        $name = fake()->unique()->randomElement($tags);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->randomElement(['blue', 'green', 'yellow', 'red', 'purple', 'pink', 'indigo', 'gray']),
            'usage_count' => fake()->numberBetween(0, 1000),
            'is_featured' => fake()->boolean(10), // 10% chance of being featured
        ];
    }

    /**
     * Indicate that the tag is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'usage_count' => fake()->numberBetween(100, 1000),
        ]);
    }

    /**
     * Indicate that the tag is popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => fake()->numberBetween(500, 2000),
        ]);
    }
}
