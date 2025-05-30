<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_methods';

    protected $guarded = ['id'];

    public function relatedTransactions()
    {
        return $this->hasMany(Transaction::class, 'payment_method_id');
    }
}
