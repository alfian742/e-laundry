<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryMethod extends Model
{
    use HasFactory;

    protected $table = 'delivery_methods';

    protected $guarded = ['id'];

    public function deliveryOrders()
    {
        return $this->hasMany(Order::class, 'delivery_method_id');
    }
}
