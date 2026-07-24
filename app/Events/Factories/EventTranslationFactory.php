<?php

namespace App\Events\Factories;

use App\Events\Models\Event;
use App\Events\Models\EventTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventTranslation>
 */
final class EventTranslationFactory extends Factory
{
    /** @var class-string<EventTranslation> */
    protected $model = EventTranslation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'event_id' => Event::factory(),
            'locale' => 'en',
            'title' => $title,
            'slug' => fake()->unique()->slug(4),
            'summary' => fake()->sentence(12),
            'body' => fake()->paragraphs(3, true),
        ];
    }
}
