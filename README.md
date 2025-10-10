# PimonoWallet

## Setup

* Requires MySQL/Postgres due to use of database row level locking
* Set DB_* values in .env
* Set Pusher credentials in .env (pasted in email if required)

>git clone git@github.com:iainco/pimono-wallet.git<br/>
cp .env.example .env<br/>
php artisan migrate --seed<br/>
composer dev

Log in using `iainco@me.com` & `hellopimono` or `cenk@pimono.ae.test` & `hello` (or you can register too)

## Notes

Opted to use Inertia.js vs vanilla Vue.js, I enjoy working with Inertia.js and how you can rapidly build SPAs with less boilerplate, I hope this is acceptable. I made allowances for this for example when creating a Transaction, the `transactions` prop from the `TransactionsController::index` method is not needlessy reloaded/queried again because the new `Transaction` object will be provided by Pusher.

To improve concurrency from the context of 100s of transactions per second (perhaps originating from a mobile app) we would want to introduce using the Queue to process these Transactions, and introduce a `status` property which can be `pending`, `processing`, `failed` or `complete` and then add more Event types to alert the user depending on the status. This would also allow us to show a pending (and allow cancelling of transactions) and other statuses on the main Transcations table. Each job can be made unique and querying the database will still use the same row level locking but gives us the ability to retry failed transactions depending on the failure reason.

We would also look to create a `CreateTransaction` Action class that can be executed either from the web via Inertia or from an app via `POST /api/transactions` with a Sanctum token.

The current solution/codebase guards against simultaneous transactions between 2 users ensuring atomicity.

## Next Steps

- Typeahead/autocomplete field for email address field in `CreateTransaction`
- Show "Balance after transaction: x" on `CreateTransaction` which updates before user submits form
- Add nullable commission_rate to users table that will override `config('app.commission_rate')` when set
- Allow searching/sorting/filtering etc. of Transactions
- Proper currency support/conversions/multi-lingual support (currently always assumes English/GBP)
- Pest concurrency tests with --parallel to simulate simultaneous transactions
- Pest browser tests

## Known Issues

- If you are on page > 1 and a new transaction comes in via Pusher it will display at the top of the current page, when it should be page 1

## Thank you!

Thank you for your time and consideration :)
