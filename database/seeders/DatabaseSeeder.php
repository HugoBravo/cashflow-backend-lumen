<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserRoleSeeder::class);
        \App\Models\User::factory(1)->create();
        $this->call(CurrencySeeder::class);
        $this->call(CashCategorySeeder::class);
        $this->call(CashConceptSeeder::class);
        $this->call(CashClosureSeeder::class);
        $this->call(CashClosureBalanceSeeder::class);
    }
} 