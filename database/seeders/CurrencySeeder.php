<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            [
                'name'=> 'PESO',
                'symbol' => 'CLP',
                'image' => 'assets/flags/cl.png',
                'status' => true,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'name'=> 'DOLAR',
                'symbol' => 'USD',
                'image' => 'assets/flags/us.png',
                'status' => true,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]
        ];

        DB::table('currencies')->insert( $currencies );
    }
}