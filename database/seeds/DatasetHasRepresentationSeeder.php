<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatasetHasRepresentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('dataset_has_representations')->insert([
            'datasetId'=>1,
            'representationName'=>"Graphique en anneau",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('dataset_has_representations')->insert([
            'datasetId'=>2,
            'representationName'=>"Graphique en anneau",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
