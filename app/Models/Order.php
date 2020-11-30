<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Devuelve el estado en lenguaje humano
     *
     * @return mixed
    */
    public function getStatusAttribute()
    {
       $estado = $this->attributes['status'];
        return Config::get('constants.'.$estado);
    }

    public function store($data){
        $this->fill($data)->save();
    }
}
