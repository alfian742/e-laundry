<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProofOfPayment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = PaymentMethod::all();

        foreach (Order::all() as $order) {
            $remainingAmount = $order->final_price;
            $transactions = [];

            // Maksimal 1-3 kali pembayaran
            $numTransactions = rand(1, 3);

            for ($i = 0; $i < $numTransactions && $remainingAmount > 0; $i++) {
                $paymentMethod = $paymentMethods->random();

                // Jika transaksi terakhir, bayar sisa semua
                if ($i == $numTransactions - 1 || $remainingAmount < 50000) {
                    $amountPaid = $remainingAmount;
                } else {
                    $amountPaid = rand(10000, min(50000, (int) $remainingAmount));
                }

                $status = $amountPaid > 0 ? 'success' : 'pending';

                $invoiceId = $status === 'success'
                    ? 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5))
                    : null;

                // Random date
                $startTimestamp = Carbon::now()->startOfMonth()->timestamp;
                $endTimestamp = Carbon::now()->endOfMonth()->timestamp;
                $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);
                $randomDate = Carbon::createFromTimestamp($randomTimestamp)->format('Y-m-d H:i:s');

                $transactions[] = [
                    'invoice_id' => $invoiceId,
                    'order_id' => $order->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount_paid' => $amountPaid,
                    'status' => $status,
                    'notes' => null,
                    // 'paid_at' => $amountPaid > 0 ? now() : null,
                    'paid_at' => $amountPaid > 0 ? $randomDate : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $remainingAmount -= $amountPaid;
            }

            // Simpan semua transaksi sekaligus
            Transaction::insert($transactions);

            // Update payment_status di Order
            $totalPaid = array_sum(array_column($transactions, 'amount_paid'));

            if ($totalPaid >= $order->final_price) {
                $order->update(['payment_status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $order->update(['payment_status' => 'partial']);
            } else {
                $order->update(['payment_status' => 'unpaid']);
            }

            // Cek jika ada metode pembayaran online, buat proof of payment
            foreach ($transactions as $transaction) {
                $paymentMethod = PaymentMethod::find($transaction['payment_method_id']);
                if ($paymentMethod && $paymentMethod->payment_type === 'online') {
                    ProofOfPayment::create([
                        'transaction_id' => Transaction::where('order_id', $order->id)
                            ->where('payment_method_id', $paymentMethod->id)
                            ->latest()
                            ->value('id'),
                        'img' => 'default.jpg',
                    ]);
                }
            }
        }
    }
}
