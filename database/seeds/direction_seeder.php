<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;


class direction_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('directions')->insert([
            'direction'=>"Numérique",
            'description'=>"Direction du numérique",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('directions')->insert([
            'direction'=>"Direction Mobilites Gestion Reseaux",
            'description'=>'Direction Mobilites Gestion Reseaux',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
