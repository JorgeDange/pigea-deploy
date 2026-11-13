<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            RoleSeeder::class,
            ContentSeeder::class,
            DirectorySeeder::class,
            // FASE 5.1: depois do ContentSeeder — as subcategorias de notícias
            // (Mineração/Petróleo/Geociências) penduram nas categorias pai.
            MenuSeeder::class,
        ]);
    }
}
