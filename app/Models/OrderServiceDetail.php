<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderServiceDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_service_details';

    protected $guarded = ['id'];

    public function parentOrder()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function includedService()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function includedPromo()
    {
        return $this->belongsTo(Promo::class, 'promo_id');
    }
}
