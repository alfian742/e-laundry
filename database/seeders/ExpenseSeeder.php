<?php

namespace Database\Seeders;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'expense_category' => 'Tagihan Listrik',
                'total_amount'     => 350000,
                'paid_amount'      => 350000,
                'status'           => 'paid',
                'notes'            => 'Pembayaran listrik untuk keperluan operasional laundry.',
                'paid_at'          => Carbon::now()->subDays(5)->format('Y-m-d'),
            ],
            [
                'expense_category' => 'Tagihan Air',
                'total_amount'     => 120000,
                'paid_amount'      => 120000,
                'status'           => 'paid',
                'notes'            => 'Pembayaran tagihan air untuk proses pencucian pakaian.',
                'paid_at'          => Carbon::now()->subDays(3)->format('Y-m-d'),
            ],
            [
                'expense_category' => 'Kebutuhan Laundry',
                'total_amount'     => 250000,
                'paid_amount'      => 180000,
                'status'           => 'partial',
                'notes'            => 'Pembelian detergen, pewangi, dan plastik pembungkus pakaian.',
                'paid_at'          => Carbon::now()->subDays(2)->format('Y-m-d'),
            ],
            [
                'expense_category' => 'Gaji Karyawan',
                'total_amount'     => 2500000,
                'paid_amount'      => 0,
                'status'           => 'unpaid',
                'notes'            => 'Gaji bulanan untuk staf laundry.',
                'paid_at'          => null,
            ],
            [
                'expense_category' => 'Perawatan Mesin',
                'total_amount'     => 750000,
                'paid_amount'      => 750000,
                'status'           => 'paid',
                'notes'            => 'Biaya servis dan perawatan rutin mesin cuci & pengering.',
                'paid_at'          => Carbon::now()->subDays(7)->format('Y-m-d'),
            ],
        ];

        foreach ($data as $item) {
            Expense::create([
                'expense_category' => $item['expense_category'],
                'total_amount'     => $item['total_amount'],
                'paid_amount'      => $item['paid_amount'],
                'status'           => $item['status'],
                'notes'            => $item['notes'],
                'paid_at'          => $item['paid_at'],
            ]);
        }
    }
}
