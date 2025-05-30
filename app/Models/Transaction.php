<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected $guarded = ['id'];

    public function relatedOrder()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function usedPaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function relatedProof()
    {
        return $this->hasOne(ProofOfPayment::class, 'transaction_id');
    }
}
