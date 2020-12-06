<?php

namespace App\Lib;

use Illuminate\Support\Facades\Log;

class Helpers
{
    /**
     * Devuelve la fecha en formato local
     *
     * @param $valor
     * @param string $formato
     * @return string
     */
    public static function dateFormat($valor, $formato='d/m/Y h:i A'){
        return \Carbon\Carbon::parse($valor)->format($formato);
    }

    /**
     * Devuelve la estructura general de las respuesta de la api
     *
     * @param $data
     * @param string $status
     * @return array
     */
    public static function APIResponse($data, $status='success')
    {
        return [
            'status' => $status,
            ($status=='success')?'data':'errors' => $data
        ];
    }

    /**
     * Retorna una vista en JSON de todas las repuestas teniendo en cuenta
     * el control de errores
     *
     * @param $funcSuccess
     * @param null $funcError
     * @return \Illuminate\Http\JsonResponse
     */
    public static function ViewJSONResponse($funcSuccess, $funcError = null){
        //Si se presenta un error de validacion, lo manda por excepcion
        try {
            list ($res, $code) = $funcSuccess();
            $res = self::APIResponse($res);

        } catch (\Throwable $ex) {
            //si tiene funcion de error, la ejecuta
            if ($funcError){
                $funcError();
            }
            $code = $ex->getCode();
            $msg = $ex->getMessage();
            if ($code >= 400 && $code <=499) {
                $res = self::APIResponse($msg, 'error');
            }else{
                Log::error($msg);
                $msg = "Error desconocido";
                $res = self::APIResponse($msg, 'error');
            }
        }
        return response()->json($res, $code);
    }
}
