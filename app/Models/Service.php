<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $guarded = ['id'];

    public function promos()
    {
        return $this->belongsToMany(Promo::class, 'promo_service')->withTimestamps();
    }

    public function serviceDetails()
    {
        return $this->hasMany(OrderServiceDetail::class, 'service_id');
    }
}
