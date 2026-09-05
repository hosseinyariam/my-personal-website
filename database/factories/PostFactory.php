<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Support\Slugger;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = \App\Models\Post::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Slugger::make($title),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => PostStatus::Draft, 'published_at' => null]);
    }

    /** پستِ زمان‌بندی‌شده با تاریخ آینده — نباید عمومی شود. */
    public function scheduled(): static
    {
        return $this->state([
            'status' => PostStatus::Published,
            'published_at' => now()->addWeek(),
        ]);
    }
}
