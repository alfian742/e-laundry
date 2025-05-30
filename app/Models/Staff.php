<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staffs';

    protected $guarded = ['id'];

    public function userAccount()
    {
        return $this->hasOne(User::class, 'staff_id');
    }

    public function handledOrders()
    {
        return $this->hasMany(Order::class, 'staff_id');
    }
}
