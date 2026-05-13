<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('users')->delete();

        \DB::table('users')->insert(array(
            0 =>
            array(
                'id'                => 1,
                'name'              => 'Alex',
                'surname1'          => 'Romero',
                'surname2'          => NULL,
                'alias'             => 'aromero',
                'email'             => 'admin@demo.com',
                'email_verified_at' => NULL,
                'password'          => bcrypt('12345678'),
                'codigo_postal'     => '08980',
                'dni'               => '12345678A',
                'direccion'         => 'Calle Falsa 123',
                'fecha_nacimiento'  => '1990-01-01',
                'remember_token'    => NULL,
                'wallet'            => 5000,
                'created_at'        => '2025-07-25 08:51:49',
                'updated_at'        => '2025-07-25 08:51:49',
            ),
            1 =>
            array(
                'id'                => 2,
                'name'              => 'Kevin',
                'surname1'          => 'Alba',
                'surname2'          => NULL,
                'alias'             => 'Kalba',
                'email'             => 'user@demo.com',
                'email_verified_at' => NULL,
                'password'          => bcrypt('12345678'),
                'codigo_postal'     => '28001',
                'dni'               => '87654321B',
                'direccion'         => 'Calle Mayor 45',
                'fecha_nacimiento'  => '1995-06-15',
                'remember_token'    => NULL,
                'wallet'            => 2000,
                'created_at'        => '2025-07-25 08:51:49',
                'updated_at'        => '2025-07-25 08:51:49',
            ),
        ));
    }
}
