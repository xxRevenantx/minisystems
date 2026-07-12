<?php

namespace Database\Seeders;

use App\Models\ReconocimientoPermiso;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'MiniSystem',
            'email' => 'minisystem@system.com',
            'password' => bcrypt('minisystem123'),
        ]);

        ReconocimientoPermiso::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'ver' => true,
                'crear' => true,
                'editar' => true,
                'aprobar' => true,
                'descargar' => true,
                'cancelar' => true,
                'administrar' => true,
            ]
        );

        $this->call(DirectivoSeeder::class);
    }
}
