<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'direction'=>"Transport",
            'firstname'=>"Esteban",
            'lastname' => "Lhote",
            "mail" => "esteban.lhote@metapolis.fr",
            "phone" => "0558565854",
            "role" => "Référent-Métier",
            "service"=>"Transport",
            "uuid"=>"2be8c158-29a7-42b3-a9fb-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
