<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $guarded = ['id'];

    public function assignedStaff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function orderingCustomer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function deliveryOption()
    {
        return $this->belongsTo(DeliveryMethod::class, 'delivery_method_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderServiceDetail::class, 'order_id');
    }

    public function orderTransactions()
    {
        return $this->hasMany(Transaction::class, 'order_id');
    }
}
