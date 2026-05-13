<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AjustesTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('ajustes')->delete();

        \DB::table('ajustes')->insert(array(
            0 =>
            array(
                'id'          => 1,
                'clave'       => '1 euro',
                'valor'       => '100',
                'descripcion' => 'Fichas que se dan por cada euro depositado',
                'created_at'  => '2025-07-25 08:51:49',
                'updated_at'  => '2025-07-25 08:51:49',
            ),
            1 =>
            array(
                'id'          => 2,
                'clave'       => 'apuesta_minima',
                'valor'       => '10',
                'descripcion' => 'Apuesta mínima por mano en fichas',
                'created_at'  => '2025-07-25 08:51:49',
                'updated_at'  => '2025-07-25 08:51:49',
            ),
            2 =>
            array(
                'id'          => 3,
                'clave'       => 'apuesta_maxima',
                'valor'       => '1000',
                'descripcion' => 'Apuesta máxima por mano en fichas',
                'created_at'  => '2025-07-25 08:51:49',
                'updated_at'  => '2025-07-25 08:51:49',
            ),
        ));
    }
}
