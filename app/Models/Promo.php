<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $table = 'promos';

    protected $guarded = ['id'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'promo_service')->withTimestamps();
    }
}
