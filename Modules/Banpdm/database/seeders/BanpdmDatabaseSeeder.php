<?php

namespace Modules\Banpdm\Database\Seeders;

use Illuminate\Database\Seeder;

class BanpdmDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        $this->call([
            SatuanSekolahSeeder::class,
        ]);
    }
}
