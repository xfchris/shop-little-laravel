<?php

namespace App\Lib;

use Dnetix\Redirection\PlacetoPay;

class GstPlaceToPay
{
    public $placeToPay;

    public function __construct()
    {
        $this->configInicial();
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
        $reference = $order->id;
        $request = [
            'payment' => [
                'reference' => $reference,
                'description' => $producto->nombre,
                'amount' => [
                    'currency' => $producto->moneda,
                    'total' => $producto->precio,
                ],
            ],
            'expiration' => date('c', strtotime('+2 days')),
            'returnUrl' => route('aceptarPago') . $reference,
            'ipAddress' => request()->ip(),
            'userAgent' => request()->header('user-agent'),
        ];
        $response = $this->placeToPay->request($request);

        if ($response->isSuccessful()) {
            return $response->processUrl();
        } else {
            throw new \Exception($response->status()->message());
        }
    }

    public function getInfoPago()
    {

    }

    /**
     * Configuración inicial
     *
     * @throws \Dnetix\Redirection\Exceptions\PlacetoPayException
     */
    public function configInicial(){
        $placetopay = new PlacetoPay([
            'login' => env('PLACE_TO_PAY_LOGIN'),
            'tranKey' => env('PLACE_TO_PAY_TRANKEY'),
            'url' => env('PLACE_TO_PAY_URL'),
            'rest' => [
                'timeout' => 45, // (optional) 15 by default
                'connect_timeout' => 30, // (optional) 5 by default
            ]
        ]);
        $this->placeToPay = $placetopay;
    }
}
