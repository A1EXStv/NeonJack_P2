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
                'surname1' => 'Romero',
                'surname2' => NULL,
                'alias' => 'aromero',
                'email' => 'admin@demo.com',
                'email_verified_at' => NULL,
                'password' => bcrypt('12345678'),
                'remember_token' => NULL,
                'created_at' => '2025-07-25 08:51:49',
                'updated_at' => '2025-07-25 08:51:49',
                'wallet' => 100,
                // 'active_skin' => 1,
            ),
        ));


    }
}
