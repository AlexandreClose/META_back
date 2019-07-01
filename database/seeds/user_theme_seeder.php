<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class user_theme_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_theme')->insert([
            "uuid"=>"2be8c158-29a7-42b3-a9fb-de9ec266e196",
            "name"=>"Stationnement",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('user_theme')->insert([
            "uuid"=>"2be8c158-29a7-42b3-a9fb-de9ec266e196",
            "name"=>"Mobilité",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('user_theme')->insert([
            "uuid"=>"2be8c358-29a7-42b4-a9fb-de9ec266e196",
            "name"=>"Mobilité",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
