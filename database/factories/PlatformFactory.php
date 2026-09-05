<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlatformStatus;
use App\Support\Slugger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * فقط برای تست — هیچ داده‌ی ساختگی‌ای وارد seed تولید نمی‌شود (V3).
 */
class PlatformFactory extends Factory
{
    protected $model = \App\Models\Platform::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'title' => $title,
            'slug' => Slugger::make($title),
            'tagline' => fake()->sentence(),
            'problem' => fake()->paragraph(),
            'solution' => fake()->paragraph(),
            'status' => PlatformStatus::Live,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
