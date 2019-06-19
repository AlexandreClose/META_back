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
            "role" => "Administrateur",
            "service"=>"Transport",
            "uuid"=>"2be8c158-29a7-42b3-a9fb-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('users')->insert([
            'direction'=>"Transport",
            'firstname'=>"Josué",
            'lastname' => "Foucaud",
            "mail" => "josué.foucaud@metapolis.fr",
            "phone" => "0558565854",
            "role" => "Utilisateur",
            "service"=>"Voirie",
            "uuid"=>"2be8c158-29a7-42b4-a9fb-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('users')->insert([
            'direction'=>"Transport",
            'firstname'=>"Adrien",
            'lastname' => "Audemar",
            "mail" => "adrien.audemar@metapolis.fr",
            "phone" => "0558565854",
            "role" => "Utilisateur",
            "service"=>"Transport",
            "uuid"=>"2be8c458-29a7-42b4-a9fj-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('users')->insert([
            'direction'=>"Transport",
            'firstname'=>"Bastien",
            'lastname' => "Peuble",
            "mail" => "bastien.peuble@metapolis.fr",
            "phone" => "0558565854",
            "role" => "Administrateur",
            "service"=>"Voirie",
            "uuid"=>"2be8c358-29a7-42b4-a9fb-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('users')->insert([
            'direction'=>"Transport",
            'firstname'=>"Timothée",
            'lastname' => "Blanchard",
            "mail" => "timothée.blanchard@metapolis.fr",
            "phone" => "0558565854",
            "role" => "Utilisateur",
            "service"=>"Finance",
            "uuid"=>"2be8c158-29a7-43b4-a9fb-de9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        DB::table('users')->insert([
            'direction'=>"Transport",
            'firstname'=>"Florent",
            'lastname' => "Kirch",
            "mail" => "florent.kirch@metapolis.fr",
            "phone" => "0558565854",
            "role" => "Administrateur",
            "service"=>"Finance",
            "uuid"=>"2be8c158-29a7-42b4-a9fb-di9ec266e196",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

    }
}
