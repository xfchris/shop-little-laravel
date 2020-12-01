<?php

namespace App\Http\Controllers;

use App\Lib\GstPlaceToPay;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function buscarOrden(Request $request, Order $order, GstPlaceToPay $gstPlaceToPay)
    {
        //Si se presenta un error de validacion, lo manda por excepcion
        try {
            $code = 200;
            $data = $this->prepararYValidarDatos($request);

            //Compruebo si ha cambiado su estado en ptp
            if ($res = $order->where($data)->where('status', 'CREATED')->first()) {
                $estado = $gstPlaceToPay->getStatusPago($res->payment->request_id);

                $res->status = $estado->status();
                //si ha cambiado de estado, lo cambio en base de datos
                if ($res->status == 'REJECTED') {
                    $res->save();
                    $res = null;
                }else{
                    $res['url'] = $res->payment->process_url;
                }
            }

        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
            $code = $ex->getCode();
            //Si no es un error de validacion, lo registra y reporta como error 500
            if ($code != 400) {
                $code = 500;
                Log::error($msg);
                $msg = "Error desconocido";
            }
            $res = ['error' => $msg];
        }
        return response()->json($res, $code);
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
            $ordenEncontrada = $order->where($data)->where('status', 'CREATED')->first();

            if (!$ordenEncontrada) {
                DB::beginTransaction();
                $data['status'] = 'CREATED';
                if ($order->store($data)) {
                    $resPTP = $gstPlaceToPay->pagar($order, Config('constants.producto'));
                    //guardo sesion y la url de pago
                    if ($order->guardarSesion($order->id, [$resPTP->requestId, $resPTP->processUrl])) {
                        $res = ['status' => 'ok', 'msg' => $resPTP->processUrl];
                        DB::commit();
                    }
                }
            } else {
                throw new \Exception("Ya existe un registro en base de datos con los datos de esta orden", 400);
            }
        } catch (\Throwable $ex) {
            DB::rollBack();
            $msg = $ex->getMessage();
            $code = $ex->getCode();
            //Si no es un error de validacion, lo registra y reporta como error 500
            if ($code != 400) {
                $code = 500;
                Log::error($msg);
                $msg = "Error desconocido intentelo de nuevo";
            }
            $res = ['status' => 'error', 'msg' => $msg];
        }
        return response()->json($res, $code);
    }

    /**
     * Realiza una consulta en placetopay y aprueba el pago de la orden
     *
     * @param GstPlaceToPay $gstPlaceToPay
     * @param $id
     * @throws \Exception
     */
    public function aceptarPago(GstPlaceToPay $gstPlaceToPay, $id)
    {
        $res = redirect('/');
        try {
            //busco la orden
            $order = Order::find(explode('_', $id)[0]);
            //si no esta en etado pagada, la busco en ptp
            if ($order->status != Config('constants.status.PAYED')) {
                $estado = $gstPlaceToPay->getStatusPago($order->payment->request_id);
                $order->status = $estado->status();
                //si su estado esta aprobada, la apruebo en base de datos
                if ($estado->isApproved()) {
                    $res = $res->with('success', 'Pago aprobado!');
                }elseif($estado->isRejected()){
                    $order->status = 'REJECTED';
                    $res = $res->with('warning', 'Pago canelado!');
                }
                $order->save();
            }
        } catch (\Throwable $ex) {
            Log::error($ex->getMessage());
            $res = $res->with('warning', 'Error desconocido');
        }
        return $res;
    }

    /**
     * Metodo que actualiza pagos que quedaron pendiente
     * Nota: se puede optimizar añadiendo la consulta a unas variables y ejecutarlas
     * todas en un solo query.
     *
     * @param GstPlaceToPay $gstPlaceToPay
     * @throws \Exception
     */
    public function actualizarPagosPendientes(GstPlaceToPay $gstPlaceToPay){
        //obtengo pagos pendientes
        $ordenes = Order::where('status','PENDING')->get();

        $estadosActualizados = [];
        foreach ($ordenes as $orden) {
            //realizo una consulta en ptp
            $estado = $gstPlaceToPay->getStatusPago($orden->payment->request_id);
            if ($orden->status != Config('constants.status.'.$estado->status())){
                $orden->status = ($estado->status()!='APPROVED')?:'PAYED';
                $orden->save();
                $estadosActualizados[] = $orden->id;
            }
        }
        return $estadosActualizados;
    }
    /**
     * Realiza una consulta en placetopay y rechaza el pago de la orden
     *
     * @param GstPlaceToPay $gstPlaceToPay
     * @param $id
     */
    public function cancelarPago(GstPlaceToPay $gstPlaceToPay, $id)
    {
        try {
            //busco la orden
            $order = Order::find(explode('_', $id)[0]);
            //si esta en estado de creacion, la busco en ptp
            if ($order->status == Config('constants.status.CREATED')) {
                $estado = $gstPlaceToPay->getStatusPago($order->payment->request_id);
                //si esta en estado rechazado, rechazar pago
                if ($estado->isRejected()) {
                    $order->status = 'REJECTED';
                    $order->save();
                }
            }
        } catch (\Throwable $ex) {
            Log::error($ex->getMessage());
        }
        return redirect('/')->with('warning', 'Pago canelado!');;
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
        $rules = [
            'customer_name' => 'required|max:80',
            'customer_email' => 'required|email|max:120',
            'customer_mobile' => 'required|max:40',
        ];
        $valid = Validator::make($data, $rules);
        if ($valid->fails()) {
            $errores = $valid->errors()->all();
            throw new \Exception(implode(', ', $errores), 400);
        }
        return $data;
    }
}
