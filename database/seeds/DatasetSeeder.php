<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatasetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('datasets')->insert([
            'contributor'=>"Esteban Lhote",
            'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
            'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'creator' => "Esteban Lhote",
            'description' => "Une liste des feux tricolores de Toulouse",
            'id' => 1,
            'license' => "MIT",
            "name" => "feux_tricolores",
            "producer" => "Esteban Lhote",
            "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),
            "updated_date" => Carbon::now()->format('Y-m-d H:i:s'),
            "user" => "Esteban Lhote",
            "validated"=> 0,
            "visibility" => "all",
            "themeName"=>"Finance",
            "realtime" => false,
            "conf_ready" => true,
            "upload_ready" => true,
        ]);

        DB::table('datasets')->insert([
            'contributor'=>"Esteban Lhote",
            'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
            'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'creator' => "Esteban Lhote",
            'description' => "Une liste des places de parking pour personne a mobilité réduite",
            'id' => 2,
            'license' => "MIT",
            "name" => "personne-mobilite-reduite",
            "producer" => "Esteban Lhote",
            "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),
            "updated_date" => Carbon::now()->format('Y-m-d H:i:s'),
            "user" => "Esteban Lhote",
            "validated"=> 0,
            "themeName"=>"Finance",
            "visibility" => "admin_only",
            "realtime" => false,
            "conf_ready" => true,
            "upload_ready" => true,
        ]);
    }

}
