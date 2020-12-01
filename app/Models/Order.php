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
       $res = Config::get('constants.status.'.$estado);
        return $res?:$estado;
    }

    public function store($data){
        return $this->fill($data)->save();
    }

    public function getReference(){
        return $this->id.'_'.$this->created_at->timestamp;
    }

    public function guardarSesion($orderId, $resPTP){
        $data = [
            'order_id'=>$orderId,
            'request_id'=>$resPTP[0],
            'process_url'=>$resPTP[1],
        ];
        return $this->payment()->create($data);
    }

    public function payment(){
        return $this->hasOne('App\Models\Payment');
    }
}
