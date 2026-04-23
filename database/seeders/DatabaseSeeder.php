<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\Herd;
use App\Models\RuralProducer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@agro.test',
            'password' => 'password',
        ]);

        User::factory()->viewer()->create([
            'name' => 'Viewer User',
            'email' => 'viewer@agro.test',
            'password' => 'password',
        ]);

        // RuralProducer::factory(6)->create()->each(function (RuralProducer $producer): void {
        //     Farm::factory(random_int(1, 3))
        //         ->for($producer)
        //         ->create()
        //         ->each(function (Farm $farm): void {
        //             Herd::factory(random_int(1, 3))
        //                 ->for($farm)
        //                 ->create();
        //         });
        // });
    }
}
