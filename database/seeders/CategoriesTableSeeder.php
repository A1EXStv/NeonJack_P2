<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('categories')->delete();
        
        \DB::table('categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Tecnología',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Programación',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Diseño Web',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Tutoriales',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Noticias',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Opinión',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Recursos',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'Laravel',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'Vue.js',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'General',
            ),
        ));
        
        
    }
}