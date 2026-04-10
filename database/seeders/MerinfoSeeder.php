<?php

namespace Database\Seeders;

use App\Models\Merinfo;
use Illuminate\Database\Seeder;

class MerinfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // <architect:seed>
        Merinfo::factory()->count(10)->create();
        // </architect:seed>
    }
}
