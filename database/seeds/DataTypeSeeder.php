<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DataTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('data_types')->insert([
            'name'=>"Chaîne de caractères",
            'description'=>"Chaîne de caractères",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('data_types')->insert([
            'name'=>"Nombre",
            'description'=>"Nombre",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('data_types')->insert([
            'name'=>"Date",
            'description'=>"Date",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('data_types')->insert([
            'name'=>"GeoShape",
            'description'=>"GeoShape",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('data_types')->insert([
            'name'=>"GeoPoint",
            'description'=>"GeoPoint",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
