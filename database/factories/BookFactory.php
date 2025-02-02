<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // Crea un usuario si no existe
            'title' => $this->faker->sentence(4), // Genera un título aleatorio
            'description' => $this->faker->paragraph(3), // Genera una descripción aleatoria
        ];
    }
}
