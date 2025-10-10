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

        try {
            $transaction = DB::transaction(function () {
                $senderEmail = auth()->user()->email;
                $receiverEmail = request('email');

                $users = User::query()
                    ->whereIn('email', [$senderEmail, $receiverEmail])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('email');

                $sender = $users[$senderEmail];
                $receiver = $users[$receiverEmail];

                $amount = (int) round(request('amount') * 100);
                $commissionFee = (int) round($amount * config('app.commission_rate'));
                $totalAmount = $amount + $commissionFee;

                if ($sender->balance < $totalAmount) {
                    throw ValidationException::withMessages([
                        'amount' => 'You cannot send more than your available balance.',
                    ]);
                }

                $sender->decrement('balance', $totalAmount);
                $receiver->increment('balance', $amount);

                return Transaction::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'amount' => $amount,
                    'commission_fee' => $commissionFee,
                ]);
            });

            broadcast(new TransactionCreated($transaction));
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'generic' => 'An error occurred during your request and your transaction will not be processed, please try again.',
            ]);
        }

        return back();
    }
}
