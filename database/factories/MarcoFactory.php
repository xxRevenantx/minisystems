<?php

namespace Database\Factories;

use App\Models\Marco;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Marco>
 */
class MarcoFactory extends Factory
{
    protected $model = Marco::class;

    public function definition(): array
    {
        $archivo = 'marco-demo-'.$this->faker->unique()->numberBetween(1, 99999).'.png';

        return [
            'nombre' => $this->faker->words(3, true),
            'marco' => $archivo,
            'marco_desktop' => $archivo,
            'marco_mobile' => null,
            'descripcion' => $this->faker->sentence(),
            'categoria' => $this->faker->randomElement(['General', 'Institucional', 'Clausura']),
            'activo' => true,
            'ancho_desktop' => 2058,
            'alto_desktop' => 1365,
            'formato_desktop' => 'png',
            'transparencia_desktop' => true,
            'tags' => ['demo'],
            'orden' => 0,
            'usos' => 0,
        ];
    }
}
