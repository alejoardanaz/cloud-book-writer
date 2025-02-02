<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition()
    {
        return [
            'book_id' => Book::factory(),
            'parent_id' => null,
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(5),
        ];
    }

    public function childOf(Section $parent)
    {
        return $this->state(fn (array $attributes) => [
            'book_id' => $parent->book_id,
            'parent_id' => $parent->id,
        ]);
    }
}
