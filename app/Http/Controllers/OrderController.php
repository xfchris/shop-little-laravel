<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderFoundException;
use App\Http\Requests\OrdenRequest;
use App\Lib\GstPlaceToPay;
use App\Lib\Helpers;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Lista las ordenes de la aplicacion
     */
    public function listarOrdenes()
    {
        $ordenes = Order::orderBy('created_at', 'DESC')->get();
        return view('ordenes', compact('ordenes'));
    }

    /**
     * API: Busca una orden con los datos del formulario
     *
     * @param OrdenRequest $request
     * @param Order $order
     * @param GstPlaceToPay $gstPlaceToPay
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarOrden(OrdenRequest $request, Order $order, GstPlaceToPay $gstPlaceToPay)
    {
        //Busca una orden de pago activa, si la encuentra, la retorna con su url
        try{
            $res = $order->where($request->all())->whereIn('status', ['CREATED', 'PENDING'])->first();
            //Compruebo si ha cambiado su estado en ptp
            if ($res) {
                $estado = $gstPlaceToPay->getStatusPago($res->payment->request_id);
                $res->status = $estado->status();

                //si ha cambiado de estado, lo cambio en base de datos
                if ($res->status == 'REJECTED') {
                    $res->save();
                    $res = null;
                }
            }
            $response = Helpers::ViewAPIResponse($res);
        } catch (\Throwable $ex) {
            $response = Helpers::ViewAPIResponse($res, $ex);
        }
        return $response;
    }

    /**
     * API: Guarda la orden e inicia el pago en la pasarela
     *
     * @param Request $request
     * @param OrdenRequest $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function iniciarPago(OrdenRequest $request, Order $order, GstPlaceToPay $gstPlaceToPay)
    {
        try{
            $data = $request->all();
            $ordenEncontrada = $order->where($data)->whereIn('status', ['CREATED', 'PENDING'])->first();
            DB::beginTransaction();

            // Si encuentra la orden con los mismos datos e iniciada, genera una excepcion
            throw_if($ordenEncontrada, OrderFoundException::class);

            $data['status'] = 'CREATED';
            if ($order->store($data)) {
                $resPTP = $gstPlaceToPay->pagar($order, config('constants.producto'));
                //guardo sesion y la url de pago
                if ($order->guardarSesion($order->id, [$resPTP->requestId, $resPTP->processUrl])) {
                    $res = ['url' => $resPTP->processUrl];
                    DB::commit();
                }
            }
            $response = Helpers::ViewAPIResponse($res);
        } catch (\Throwable $ex) {
            DB::rollBack();
            $response = Helpers::ViewAPIResponse(null, $ex->getCode(), $ex);
        }
        return $response;
    }

    /**
     * Realiza una consulta en placetopay y aprueba el pago de la orden
     *
     * @param GstPlaceToPay $gstPlaceToPay
     * @param int $id
     * @throws \Exception
     */
    public function aceptarPago(GstPlaceToPay $gstPlaceToPay, int $id)
    {
        $res = redirect('/');
        try {
            //busco la orden
            $order = Order::find($id);

            //si no esta en etado pagada, la busco en ptp
            if ($order->status != 'PAYED') {
                $estado = $gstPlaceToPay->getStatusPago($order->payment->request_id);
                $order->status = $estado->status();
                //si su estado esta aprobada, la apruebo en base de datos
                if ($estado->isApproved()) {
                    $order->status = 'PAYED';
                    $res = $res->with('success', 'Pago aprobado!');
                } elseif ($estado->isRejected()) {
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
     * Muestra una orden en pantalla
     *
     * @param $id
     */
    public function mostrarOrden(int $id){
        $orden = Order::select('id','customer_name','customer_email','customer_mobile','status')->findOrFail($id);
        $orden->url = $orden->payment()->select('process_url')->first()->process_url;
        $orden->status = Helpers::getEstadoPago($orden->status);
        $producto = config('constants.producto');
        return view('orden', compact('orden', 'producto'));
    }

    /**
     * Realiza una consulta en placetopay y rechaza el pago de la orden
     *
     * @param GstPlaceToPay $gstPlaceToPay
     * @param int $id
     */
    public function cancelarPago(GstPlaceToPay $gstPlaceToPay, int $id)
    {
        try {
            //busco la orden
            $order = Order::find($id);
            //si esta en estado de creacion, la busco en ptp
            if ($order->status == 'CREATED') {
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
        return redirect('/')->with('warning', 'Pago canelado!');
    }
}
