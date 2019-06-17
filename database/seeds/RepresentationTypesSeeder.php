<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RepresentationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('representation_types')->insert([
            'name'=>"Tableau",
            'srcBegin'=>"assets/images/account/settings/statistics-table-",
            "img"=>"assets/images/account/settings/statistics-table-light.svg",
            "description"=>"Tableau de données",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Graphique en colonnes",
            'srcBegin'=>"assets/images/account/settings/statistics-column-",
            "img"=>"assets/images/account/settings/statistics-column-light.svg",
            "description"=>"Graphique en colonnes",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Graphique en barres",
            'srcBegin'=>"assets/images/account/settings/statistics-bar-chart-",
            "img"=>"assets/images/account/settings/statistics-bar-chart-light.svg",
            "description"=>"Graphique en barres",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Graphique en courbes",
            'srcBegin'=>"assets/images/account/settings/statistics-line-",
            "img"=>"assets/images/account/settings/statistics-line-light.svg",
            "description"=>"Graphique en courbes",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Somme éléments",
            'srcBegin'=>"assets/images/account/settings/statistics-the-sum-of-mathematical-symbol-",
            "img"=>"assets/images/account/settings/statistics-the-sum-of-mathematical-symbol-light.svg",
            "description"=>"Somme éléments",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Carte",
            'srcBegin'=>"assets/images/account/settings/statistics-map-",
            "img"=>"assets/images/account/settings/statistics-map-light.svg",
            "description"=>"Carte",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Graphique en secteur",
            'srcBegin'=>"assets/images/account/settings/statistics-camembert-",
            "img"=>"assets/images/account/settings/statistics-camembert-light.svg",
            "description"=>"Graphique en secteur",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Graphique en anneau",
            'srcBegin'=>"assets/images/account/settings/statistics-donut-",
            "img"=>"assets/images/account/settings/statistics-donut-light.svg",
            "description"=>"Graphique en anneau",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Graphique en radar",
            'srcBegin'=>"assets/images/account/settings/statistics-radar-pentagon-",
            "img"=>"assets/images/account/settings/statistics-radar-pentagon-light.svg",
            "description"=>"Graphique en radar",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('representation_types')->insert([
            'name'=>"Graphique en radar polaire",
            'srcBegin'=>"assets/images/account/settings/statistics-radar-polaire-",
            "img"=>"assets/images/account/settings/statistics-radar-polaire-light.svg",
            "description"=>"Graphique en secteur",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

    }
}
