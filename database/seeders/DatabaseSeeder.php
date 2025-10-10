<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Iain',
                'email' => 'iainco@me.com',
                'password' => Hash::make('hellopimono'),
                'balance' => 100_00,
            ]);

        User::factory()
            ->withoutTwoFactor()
            ->create([
                'name' => 'Cenk',
                'email' => 'cenk@pimono.ae.test',
                'password' => Hash::make('hello'),
                'balance' => 100_00,
            ]);

        // User balances above are fine set as 100 as it represents them being 100 after the following transactions:
        Transaction::factory()
            ->count(8)
            ->create();
    }
}
