<?php

namespace App\Lib;

use Illuminate\Support\Facades\Log;

class Helpers
{
    private static  $estados = null;

    public static function getEstadoPago($codigo=null){
        if (!self::$estados){
            self::$estados = config('constants.status');
        }
        return self::$estados[$codigo];
    }
    /**
     * Devuelve la fecha en formato local
     *
     * @param $valor
     * @param string $formato
     * @return string
     */
    public static function dateFormat($valor, $formato = 'd/m/Y h:i A')
    {
        return \Carbon\Carbon::parse($valor)->format($formato);
    }

    /**
     * Devuelve la estructura general de las respuesta de la api
     *
     * @param $data
     * @param string $status
     * @return array
     */
    public static function APIResponse($data, $status = 'success')
    {
        return [
            'status' => $status,
            ($status == 'success') ? 'data' : 'errors' => $data
        ];
    }

    /**
     * Retorna una vista en JSON de todas las repuestas teniendo en cuenta
     * el control de errores
     *
     * @param $res
     * @param int $code
     * @param \Throwable|null $objError
     * @return \Illuminate\Http\JsonResponse
     */
    public static function ViewAPIResponse($res, $code = 200, \Throwable $objError = null)
    {
        if (!$objError) {
            $res = self::APIResponse($res);
        } else {
            $msg = $objError->getMessage();
            if (!($code >= 400 && $code <= 499)) {
                $code = 500;
                Log::error($msg);
                $msg = "Error desconocido";
            }
            $res = self::APIResponse($msg, 'error');
        }
        return response()->json($res, $code);
    }
}
