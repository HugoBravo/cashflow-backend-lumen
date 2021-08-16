<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name'=> 'ROOT_ROLE',
            ],
            [
                'name'=> 'ADMIN_ROLE',
            ],
            [
                'name'=> 'CAPTURE_ROLE',
            ]
        ];

        DB::table('user_roles')->insert( $roles );
    }
}
