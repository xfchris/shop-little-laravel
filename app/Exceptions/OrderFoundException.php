<?php

namespace App\Exceptions;

use App\Lib\Helpers;
use Exception;
use Illuminate\Http\JsonResponse;

class OrderFoundException extends Exception
{
    protected $code = 400;
    protected $message = "Ya existe un registro en base de datos con los datos de esta orden";

    /**
     * Vista del error personalizado
     *
     * @return JsonResponse
     */
    public function render()
    {
        return new JsonResponse(Helpers::APIResponse($this->message, 'error'), $this->code);
    }
}
