<?php

namespace Database\Factories;

use App\Models\Gist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gist>
 */
class GistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $languages = ['php', 'javascript', 'python', 'java', 'css', 'html', 'sql', 'bash'];
        $language = fake()->randomElement($languages);
        
        return [
            'user_id' => User::factory(),
            'github_gist_id' => fake()->optional()->uuid(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'content' => $this->generateContent($language),
            'language' => $language,
            'filename' => $this->generateFilename($language),
            'is_public' => fake()->boolean(80), // 80% chance of being public
            'is_synced' => fake()->boolean(30), // 30% chance of being synced
            'views_count' => fake()->numberBetween(0, 1000),
            'likes_count' => fake()->numberBetween(0, 100),
            'comments_count' => fake()->numberBetween(0, 50),
            'favorites_count' => fake()->numberBetween(0, 25),
            'github_created_at' => fake()->optional()->dateTimeBetween('-1 year'),
            'github_updated_at' => fake()->optional()->dateTimeBetween('-6 months'),
        ];
    }

    /**
     * Indicate that the gist is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the gist is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the gist is synced with GitHub.
     */
    public function synced(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_synced' => true,
            'github_gist_id' => fake()->uuid(),
            'github_created_at' => fake()->dateTimeBetween('-1 year'),
            'github_updated_at' => fake()->dateTimeBetween('-6 months'),
        ]);
    }

    /**
     * Indicate that the gist is popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views_count' => fake()->numberBetween(500, 5000),
            'likes_count' => fake()->numberBetween(50, 500),
            'comments_count' => fake()->numberBetween(10, 100),
            'favorites_count' => fake()->numberBetween(25, 200),
        ]);
    }

    /**
     * Generate content based on language.
     */
    private function generateContent(string $language): string
    {
        return match ($language) {
            'php' => "<?php\n\necho 'Hello, World!';\n\n// " . fake()->sentence(),
            'javascript' => "console.log('Hello, World!');\n\n// " . fake()->sentence(),
            'python' => "print('Hello, World!')\n\n# " . fake()->sentence(),
            'java' => "public class HelloWorld {\n    public static void main(String[] args) {\n        System.out.println(\"Hello, World!\");\n    }\n}",
            'css' => ".hello {\n    color: #333;\n    font-size: 16px;\n}\n\n/* " . fake()->sentence() . " */",
            'html' => "<!DOCTYPE html>\n<html>\n<head>\n    <title>Hello</title>\n</head>\n<body>\n    <h1>Hello, World!</h1>\n</body>\n</html>",
            'sql' => "SELECT * FROM users WHERE active = 1;\n\n-- " . fake()->sentence(),
            'bash' => "#!/bin/bash\n\necho 'Hello, World!'\n\n# " . fake()->sentence(),
            default => fake()->paragraph(),
        };
    }

    /**
     * Generate filename based on language.
     */
    private function generateFilename(string $language): string
    {
        $extensions = [
            'php' => '.php',
            'javascript' => '.js',
            'python' => '.py',
            'java' => '.java',
            'css' => '.css',
            'html' => '.html',
            'sql' => '.sql',
            'bash' => '.sh',
        ];

        $basename = fake()->slug(2);
        $extension = $extensions[$language] ?? '.txt';

        return $basename . $extension;
    }
}
