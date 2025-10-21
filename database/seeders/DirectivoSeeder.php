<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DirectivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $directivos = [
            ['titulo' => 'M.C.', 'nombre' => 'José Rubén Solórzano Carbajal', 'cargo' => 'Rector'],
            ['titulo' => 'M.S.P.', 'nombre' => 'Silvia Agustín Magaña', 'cargo' => 'Directora General'],
            ['titulo' => 'M.C.', 'nombre' => 'Angélica Ocampo Agustín', 'cargo' => 'Directora de Primaria y Secundaria'],
            ['titulo' => 'Lic.', 'nombre' => 'Mariano Marcelo Mendez', 'cargo' => 'Subdirector'],
        ];

        foreach ($directivos as $directivo) {
            \App\Models\Directivo::create($directivo);
        }


    }
}
