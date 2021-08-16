<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashConceptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $concepts = [
            [
                'type'=> 1,
                'category_id' => 1,
                'concept' => 'Ventas Contado',
                'status' => true,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'type'=> 1,
                'category_id' => 1,
                'concept' => 'Ventas Credito',
                'status' => true,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'type'=> 2,
                'category_id' => 3,
                'concept' => 'Materias Primas',
                'status' => true,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
            [
                'type'=> 2,
                'category_id' => 4,
                'concept' => 'Otros',
                'status' => true,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ],
        ];

        DB::table('cash_concepts')->insert( $concepts );
    }
}
