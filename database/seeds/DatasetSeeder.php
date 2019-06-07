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
            'description' => "Une liste des chantiers en cours",
            'id' => 1,
            'license' => "MIT",
            "name" => "chantiers-en-cours",
            "producer" => "Esteban Lhote",
            "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),
            "updated_date" => Carbon::now()->format('Y-m-d H:i:s'),
            "user" => "Esteban Lhote",
            "validated"=> 0,
            "visibility" => "all",
            "realtime" => false
        ]);

        DB::table('datasets')->insert([
            'contributor'=>"Esteban Lhote",
            'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
            'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'creator' => "Esteban Lhote",
            'description' => "Une liste des horodateurs",
            'id' => 2,
            'license' => "MIT",
            "name" => "horodateurs",
            "producer" => "Esteban Lhote",
            "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),
            "updated_date" => Carbon::now()->format('Y-m-d H:i:s'),
            "user" => "Esteban Lhote",
            "validated"=> 0,
            "visibility" => "all",
            "realtime" => false
        ]);
    }

}
