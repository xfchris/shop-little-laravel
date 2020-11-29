<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function listarOrdenes(){
        $ordenes = Order::get();
        return view('ordenes', compact('ordenes'));
    }
}
