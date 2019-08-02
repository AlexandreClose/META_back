<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(DatasetSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DataTypeSeeder::class);
        $this->call(RepresentationTypesSeeder::class);
        $this->call(ThemeSeeder::class);
        $this->call(user_theme_seeder::class);
        $this->call(direction_seeder::class);
        $this->call(service_seeder::class);
        $this->call(tag_seeder::class);
        $this->call(user_theme_seeder::class);
        //$this->call(Auth_seeder::class);
        //$this->call(DatasetHasRepresentationSeeder::class);
    }
}
