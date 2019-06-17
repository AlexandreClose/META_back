<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class Auth_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('auth_users')->insert([
            'uuid'=>"2be8c158-29a7-42b3-a9fb-de9ec266e196",
            'id'=>"1",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('auth_users')->insert([
            'uuid'=>"2be8c158-29a7-42b3-a9fb-de9ec266e196",
            'id'=>"2",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
