<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    use HasFactory;

    protected $table = 'customer_reviews';

    protected $guarded = ['id'];

    public function reviewingCustomer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
