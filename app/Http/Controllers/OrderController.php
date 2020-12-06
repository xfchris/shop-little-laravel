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
     * @param Request $request
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
     * @param Order $order
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
                $resPTP = $gstPlaceToPay->pagar($order, Config('constants.producto'));
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
     * Metodo que actualiza pagos que quedaron pendiente
     * Nota: se puede optimizar añadiendo la consulta a unas variables y ejecutarlas
     * todas en un solo query.
     *
     * @param GstPlaceToPay $gstPlaceToPay
     * @throws \Exception
     */
    public function actualizarPagosPendientes(GstPlaceToPay $gstPlaceToPay)
    {
        //obtengo pagos en estado pendiente y/o vencidos
        $ordenes = Order::where('status', 'PENDING')->get();

        $estadosActualizados = [];
        foreach ($ordenes as $orden) {
            //realizo una consulta en ptp
            $estado = $gstPlaceToPay->getStatusPago($orden->payment->request_id);
            if ($orden->status != Config('constants.status.' . $estado->status())) {
                $orden->status = ($estado->status() != 'APPROVED') ?: 'PAYED';
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
        return redirect('/')->with('warning', 'Pago canelado!');
    }
}
