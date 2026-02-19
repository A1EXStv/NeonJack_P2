<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

//     /**
//      * Auto generated seed file
//      *
//      * @return void
//      */
    public function run()
    {


        \DB::table('users')->delete();

        \DB::table('users')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Alex',
                'apellido1' => 'Romero',
                'apellido2' => NULL,
                'alias' => 'aromero',
                'correo' => 'admin@demo.com',
                'verificar_correo' => NULL,
                'contrasena' => bcrypt('12345678'),
                'remember_token' => NULL,
                'created_at' => '2025-07-25 08:51:49',
                'updated_at' => '2025-07-25 08:51:49',
                'cartera' => 100,
                'skin_activa' => 1,
            ),
        ));


    }
}
