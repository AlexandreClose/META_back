<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            'tid' => '1',
            'direction' => "Direction Mobilites Gestion Reseaux",
            'firstname' => "Marielle",
            'lastname' => "CAHUC",
            "mail" => "Marielle.CAHUC@toulouse-metropole.fr",
            "role" => "Référent-Métier",
            "service" => "N/A",
            "uuid" => "2be8c158-29a7-42b3-a9fb-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('users')->insert([
            'tid' => '2',
            'direction' => "Direction du numérique",
            'firstname' => "Jean-François",
            'lastname' => "CARABIN",
            "mail" => "Jean-francois.carabin@mairie-toulouse.fr",
            "role" => "Administrateur",
            "service" => "N/A",
            "uuid" => "2be8c158-29a7-42b4-a9fb-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('users')->insert([
            'tid' => '3',
            'direction' => "Direction du numérique",
            'firstname' => "Sandrine",
            'lastname' => "MATHON",
            "mail" => "sandrine.mathon@toulouse-metropole.fr",
            "role" => "Administrateur",
            "service" => "N/A",
            "uuid" => "2be8c158-29a7-42b4-a9fb-di9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('users')->insert([
            'tid' => '4',
            'direction' => "Direction Mobilites Gestion Reseaux",
            'firstname' => "François",
            'lastname' => "Julien",
            "mail" => "Francois.JULIEN@toulouse-metropole.fr",
            "role" => "Utilisateur",
            "service" => "N/A",
            "uuid" => "2be8c358-29a7-42b4-a9fb-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
