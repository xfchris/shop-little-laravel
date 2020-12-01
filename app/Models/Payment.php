<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public $timestamps = false;

    public function order(){
        return $this->belongsTo('App\Models\Order');
    }
}
