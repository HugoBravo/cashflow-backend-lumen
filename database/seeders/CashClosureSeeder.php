<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashClosureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $closure = [
            [
                'datetime'=> date("Y-m-d H:i:s"),
                'status' => true,
                'obs' => 'Cierre inicial',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
                'username' => 'admin'
            ]
        ];

        DB::table('cash_closures')->insert( $closure );
    }
}
