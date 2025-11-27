<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(rand(4, 8));
        $status = fake()->randomElement(['draft', 'published', 'published', 'published']); // 75% published
        
        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'content' => $this->generateContent(),
            'status' => $status,
            'published_at' => $status === 'published' ? fake()->dateTimeBetween('-6 months', 'now') : null,
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Generate realistic blog content.
     */
    protected function generateContent(): string
    {
        $paragraphs = [];
        $paragraphCount = rand(3, 7);
        
        for ($i = 0; $i < $paragraphCount; $i++) {
            $paragraphs[] = '<p>' . fake()->paragraph(rand(4, 8)) . '</p>';
        }
        
        return implode("\n\n", $paragraphs);
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}

