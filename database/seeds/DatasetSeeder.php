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
            "validated"=> true,
            "visibility" => "all",
            "themeName"=>"Transport",
            "realtime" => false,
            "conf_ready" => true,
            "upload_ready" => true,
            "update_frequency" => "Hebdomadaire",
            "JSON" => true,
            "GEOJSON" => false,
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
            "themeName"=>"Transport",
            "visibility" => "all",
            "realtime" => false,
            "conf_ready" => true,
            "upload_ready" => true,
            "update_frequency" => "Annuel",
            "JSON" => true,
            "GEOJSON" => false,
        ]);

        DB::table('datasets')->insert([
            'contributor'=>"Esteban Lhote",
            'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
            'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'creator' => "Esteban Lhote",
            'description' => "Orthoplan 2017",
            'id' => 3,
            'license' => "MIT",
            "name" => "orthophotoplan-2017",
            "producer" => "Esteban Lhote",
            "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),
            "updated_date" => Carbon::now()->format('Y-m-d H:i:s'),
            "user" => "Esteban Lhote",
            "validated"=> 1,
            "themeName"=>"Transport",
            "visibility" => "all",
            "realtime" => false,
            "conf_ready" => true,
            "upload_ready" => true,
            "update_frequency" => "Annuel",
            "JSON" => true,
            "GEOJSON" => true,
        ]);

        DB::table('datasets')->insert([
            'contributor'=>"Esteban Lhote",
            'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
            'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'creator' => "Esteban Lhote",
            'description' => "cimetiere-blagnac",
            'id' => 4,
            'license' => "MIT",
            "name" => "cimetiere-blagnac",
            "producer" => "Esteban Lhote",
            "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),
            "updated_date" => Carbon::now()->format('Y-m-d H:i:s'),
            "user" => "Esteban Lhote",
            "validated"=> 1,
            "themeName"=>"Transport",
            "visibility" => "all",
            "realtime" => false,
            "conf_ready" => true,
            "upload_ready" => true,
            "update_frequency" => "Annuel",
            "JSON" => true,
            "GEOJSON" => true,
        ]);
        DB::table('datasets')->insert([
            'contributor'=>"Esteban Lhote",
            'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
            'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'creator' => "Esteban Lhote",
            'description' => "Comptage routier",
            'id' => 5,
            'license' => "MIT",
            "name" => "cfg_point_mesure",
            "producer" => "Esteban Lhote",
            "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),
            "updated_date" => Carbon::now()->format('Y-m-d H:i:s'),
            "user" => "Esteban Lhote",
            "validated"=> 1,
            "themeName"=>"Transport",
            "visibility" => "all",
            "realtime" => false,
            "conf_ready" => true,
            "upload_ready" => true,
            "update_frequency" => "Annuel",
            "JSON" => true,
            "GEOJSON" => true,
        ]);
    }

}
