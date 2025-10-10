<?php

namespace App\Http\Controllers\Transactions;

use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class TransactionsController
{
    public function index()
    {
        $allTransactions = auth()
            ->user()
            ->allTransactions()
            ->with('sender', 'receiver')
            ->paginate(6);

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
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                //'max:' . round(auth()->user()->balance / (1 + config('app.commission_rate')), 2) // Unsafe to check outside DB::transaction
            ],
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')
                    ->where(fn ($query) =>
                        $query->where('id', '!=', auth()->id())
                    ),
            ],
        ], [
            'email.exists' => 'The Recipient E-mail must exist and not be your own.'
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

            $amount = (int) round(request('amount') * 100);
            $commissionFee = (int) round($amount * config('app.commission_rate'));
            $totalAmount = $amount + $commissionFee;

            if ($sender->balance < $totalAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'You cannot send more than your available balance.',
                ]);
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
        });

        return back();
    }
}
