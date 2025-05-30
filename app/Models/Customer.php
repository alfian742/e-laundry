<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $guarded = ['id'];

    public function userAccount()
    {
        return $this->hasOne(User::class, 'customer_id');
    }

    public function customerOrders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function customerReview()
    {
        return $this->hasOne(CustomerReview::class, 'customer_id');
    }
}
