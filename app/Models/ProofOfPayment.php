<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProofOfPayment extends Model
{
    use HasFactory;

    protected $table = 'proof_of_payments';

    protected $guarded = ['id'];

    public function relatedTransaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
