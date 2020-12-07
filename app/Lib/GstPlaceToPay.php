<?php

namespace App\Lib;

use Dnetix\Redirection\PlacetoPay;

class GstPlaceToPay
{
    public $placeToPay;

    public function __construct(PlacetoPay $placeToPay)
    {
        $this->placeToPay = $placeToPay;
    }

    /**
     * Inicia el pago y devuelve la url en donde el cliente debe realizar el pago
     * @param $order
     * @param $producto
     * @return mixed
     * @throws \Exception
     */
    public function pagar($order, $producto)
    {
        $reference = $order->getReference();
        $request = [
            'payment' => [
                'reference' => $reference,
                'description' => $producto['nombre'],
                'amount' => [
                    'currency' => $producto['moneda'],
                    'total' => $producto['precio'],
                ],
            ],
            'expiration' => date('c', strtotime('+'.config('constants.dias_expiracion').' days')),
            'returnUrl' => route('aceptarPago', ['id'=>$reference]),
            'cancelUrl' => route('cancelarPago', ['id'=>$reference]),
            'ipAddress' => request()->ip(),
            'userAgent' => request()->header('user-agent'),
        ];
        $response = $this->placeToPay->request($request);

        if ($response->isSuccessful()) {
            return $response;
        } else {
            throw new \Exception($response->status()->message());
        }
    }

    /**
     * Consulta en pasarela el estado actual de la transacción y lo devuelve
     * como un objeto
     *
     * Ejemplo: Para saber si está aprobado: $obj->isApproved()
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getStatusPago($id)
    {
        $response = $this->placeToPay->query($id);
        if ($response->isSuccessful()) {
            return $response->status();
        } else {
            throw new \Exception($response->status()->message());
        }
    }
}
