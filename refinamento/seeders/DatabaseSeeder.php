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

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $this->call(MaintenanceCategoriesSeeder::class);
        // $this->call(NewMaintenanceCategoriesSeeder::class);

        // $this->call([
        //     MaintenanceSubcategoriesSeeder::class,
        //     MaintenanceSubcategoriesPart2Seeder::class,
        //     MaintenanceSubcategoriesPart3Seeder::class,
        //     MaintenanceSubcategoriesPart4Seeder::class,
        //     MaintenanceSubcategoriesPart5Seeder::class,
        // ]);

        // $this->call(OilSubcategoriesSeeder::class);

        // $this->call(OilTableSubcategoriesSeeder::class);
        // $this->call(OilTableTrucksSubcategoriesSeeder::class);

        // $this->call(VehicleMakesAndModelsSeeder::class);
        // $this->call(VehicleSpecsSeeder::class);

        // $this->call(GuideCategorySeeder::class);
        // $this->call(GuideSampleSeeder::class);
        $this->call(GuideClusterSeeder::class);
    }
}
