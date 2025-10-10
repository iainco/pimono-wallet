<?php

namespace App\Http\Controllers\Transactions;

use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use RuntimeException;
use Throwable;

class TransactionsController
{
    public function index()
    {
        $allTransactions = auth()->user()->allTransactions()->with('sender', 'receiver')->get();

        return Inertia::render('Transactions', [
            'transactions' => $allTransactions
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store()
    {
        request()->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'email' => ['required', 'email', 'exists:users'],
        ]);

        DB::transaction(function () {
            $sender = User::query()
                ->whereKey(auth()->id())
                ->lockForUpdate()
                ->first();

            $receiver = User::query()
                ->where('email', request('email'))
                ->lockForUpdate()
                ->first();

            $amount = (int) request('amount') * 100;
            $commissionFee = (int) round($amount * 0.015); // TODO: don't hardcode commission
            $totalAmount = $amount + $commissionFee;

            if ($sender->balance < $totalAmount) {
                throw new RuntimeException('Balance too low.');
            }

            $sender->balance -= $totalAmount;
            $sender->save();

            $receiver->balance += $amount;
            $receiver->save();

            $transaction = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commissionFee,
            ]);

            broadcast(new TransactionCreated($transaction));
                //->toOthers();
        });

        return back();
    }
}
