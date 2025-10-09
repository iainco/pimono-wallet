<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $sender = User::query()->find(2);
        $receiver = User::query()->find(1);

        $amount = $this->faker->numberBetween(1_00, 10_00);
        $commissionFee = (int) round($amount * 0.015);

        return [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'commission_fee' => $commissionFee,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
