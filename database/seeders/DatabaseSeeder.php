<?php

namespace Database\Seeders;

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

        $this->call(InterfaceTranslationSeeder::class);
        $this->call(AccountTranslationSeeder::class);
        $this->call(ArchiveTranslationSeeder::class);
        $this->call(PhotoTranslationSeeder::class);
        $this->call(VideoTranslationSeeder::class);
        $this->call(MapTranslationSeeder::class);
        $this->call(ResearchTranslationSeeder::class);
        $this->call(ContentTranslationSeeder::class);
        $this->call(AssetTranslationSeeder::class);
    }
}
