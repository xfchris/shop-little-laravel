<?php

namespace App\Lib;

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
}
