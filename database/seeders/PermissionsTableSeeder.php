<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 24,
                'name' => 'category-create',
                'guard_name' => 'web',
            ),
            1 => 
            array (
                'id' => 26,
                'name' => 'category-delete',
                'guard_name' => 'web',
            ),
            2 => 
            array (
                'id' => 25,
                'name' => 'category-edit',
                'guard_name' => 'web',
            ),
            3 => 
            array (
                'id' => 23,
                'name' => 'category-list',
                'guard_name' => 'web',
            ),
            4 => 
            array (
                'id' => 32,
                'name' => 'course-create',
                'guard_name' => 'web',
            ),
            5 => 
            array (
                'id' => 34,
                'name' => 'course-delete',
                'guard_name' => 'web',
            ),
            6 => 
            array (
                'id' => 33,
                'name' => 'course-edit',
                'guard_name' => 'web',
            ),
            7 => 
            array (
                'id' => 31,
                'name' => 'course-list',
                'guard_name' => 'web',
            ),
            8 => 
            array (
                'id' => 21,
                'name' => 'exercise-all',
                'guard_name' => 'web',
            ),
            9 => 
            array (
                'id' => 19,
                'name' => 'exercise-create',
                'guard_name' => 'web',
            ),
            10 => 
            array (
                'id' => 22,
                'name' => 'exercise-delete',
                'guard_name' => 'web',
            ),
            11 => 
            array (
                'id' => 20,
                'name' => 'exercise-edit',
                'guard_name' => 'web',
            ),
            12 => 
            array (
                'id' => 18,
                'name' => 'exercise-list',
                'guard_name' => 'web',
            ),
            13 => 
            array (
                'id' => 6,
                'name' => 'permission-create',
                'guard_name' => 'web',
            ),
            14 => 
            array (
                'id' => 8,
                'name' => 'permission-delete',
                'guard_name' => 'web',
            ),
            15 => 
            array (
                'id' => 7,
                'name' => 'permission-edit',
                'guard_name' => 'web',
            ),
            16 => 
            array (
                'id' => 5,
                'name' => 'permission-list',
                'guard_name' => 'web',
            ),
            17 => 
            array (
                'id' => 16,
                'name' => 'post-all',
                'guard_name' => 'web',
            ),
            18 => 
            array (
                'id' => 14,
                'name' => 'post-create',
                'guard_name' => 'web',
            ),
            19 => 
            array (
                'id' => 17,
                'name' => 'post-delete',
                'guard_name' => 'web',
            ),
            20 => 
            array (
                'id' => 15,
                'name' => 'post-edit',
                'guard_name' => 'web',
            ),
            21 => 
            array (
                'id' => 13,
                'name' => 'post-list',
                'guard_name' => 'web',
            ),
            22 => 
            array (
                'id' => 2,
                'name' => 'role-create',
                'guard_name' => 'web',
            ),
            23 => 
            array (
                'id' => 4,
                'name' => 'role-delete',
                'guard_name' => 'web',
            ),
            24 => 
            array (
                'id' => 3,
                'name' => 'role-edit',
                'guard_name' => 'web',
            ),
            25 => 
            array (
                'id' => 1,
                'name' => 'role-list',
                'guard_name' => 'web',
            ),
            26 => 
            array (
                'id' => 28,
                'name' => 'task-create',
                'guard_name' => 'web',
            ),
            27 => 
            array (
                'id' => 30,
                'name' => 'task-delete',
                'guard_name' => 'web',
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'task-edit',
                'guard_name' => 'web',
            ),
            29 => 
            array (
                'id' => 27,
                'name' => 'task-list',
                'guard_name' => 'web',
            ),
            30 => 
            array (
                'id' => 10,
                'name' => 'user-create',
                'guard_name' => 'web',
            ),
            31 => 
            array (
                'id' => 12,
                'name' => 'user-delete',
                'guard_name' => 'web',
            ),
            32 => 
            array (
                'id' => 11,
                'name' => 'user-edit',
                'guard_name' => 'web',
            ),
            33 => 
            array (
                'id' => 9,
                'name' => 'user-list',
                'guard_name' => 'web',
            ),
        ));
        
        
    }
}