<?php

namespace App\Http\Controllers;

use App\Lib\GstPlaceToPay;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    /**
     * Lista las ordenes de la aplicacion
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function listarOrdenes()
    {
        $ordenes = Order::orderBy('created_at', 'DESC')->get();
        return view('ordenes', compact('ordenes'));
    }

    /**
     * Busca una orden con los datos del formulario
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarOrden(Request $request, Order $order)
    {
        //Si se presenta un error de validacion, lo manda por excepcion
        try {
            $code = 200;
            $data = $this->prepararYValidarDatos($request);
            $data = $order->where($data)->where('status', 'CREATED')->first();

        } catch (\Exception $ex) {
            $msg = $ex->getMessage();
            $code = $ex->getCode();
            //Si no es un error de validacion, lo registra y reporta como error 500
            if ($code != 400) {
                $code = 500;
                Log::error($msg);
                $msg = "Error desconocido";
            }
            $data = ['error' => $msg];
        }
        return response()->json($data, $code);
    }

    /**
     * Guarda la orden e inicia el pago en la pasarela
     *
     * @param Request $request
     * @param Order $order
     */
    public function iniciarPago(Request $request, Order $order, GstPlaceToPay $gstPlaceToPay)
    {
        try {
            $code = 200;
            $data = $this->prepararYValidarDatos($request);
            $order = $order->where($data)->where('status', 'CREATED')->first();

            if (!$order){
                if ($order->store($data)){
                    $url = $gstPlaceToPay->pagar($order, Config('constants.producto'));
                    $data = ['status' => 'ok', 'msg' => $url];
                }
            }else{
                throw new \Exception("Ya existe un registro en base de datos con los datos de esta orden");
            }
        } catch (\Exception $ex) {
            $msg = $ex->getMessage();
            $code = $ex->getCode();
            //Si no es un error de validacion, lo registra y reporta como error 500
            if ($code != 400) {
                $code = 500;
                Log::error($msg);
                $msg = "Error desconocido";
            }
            $data = ['status' => 'error', 'msg' => $msg];
        }
        return response()->json($data, $code);
    }

    /**
     * Organiza y valida los datos del post de inciarPago y buscarOrden
     *
     * @param $request
     * @return array
     * @throws \Exception
     */
    private function prepararYValidarDatos($request)
    {
        $data = [
            'customer_name' => $request->nombres,
            'customer_email' => $request->email,
            'customer_mobile' => $request->telefono,
        ];
        $rules = array(
            'customer_name' => 'required|max:80',
            'customer_email' => 'required|email|max:120',
            'customer_mobile' => 'required|max:40',
        );
        $valid = Validator::make($data, $rules);
        if ($valid->fails()) {
            $errores = $valid->errors()->all();
            throw new \Exception(implode(', ', $errores), 400);
        }
        return $data;
    }
}
