<?php

namespace App\Http\Requests;

use App\Lib\Helpers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrdenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Retorna las reglas de validacion a la solicitud.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_name' => 'required|max:80',
            'customer_email' => 'required|email|max:120',
            'customer_mobile' => 'required|max:40'
        ];
    }

    /**
     * Retorna errores con estructura base de respuestas
     *
     * @param Validator $validator
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $response = new JsonResponse(Helpers::APIResponse($validator->errors(), 'error'), 422);
        throw new ValidationException($validator, $response);
    }

    /**
     * Retorna los atributos con su nombre real
     *
     * @return string[]
     */
    public function attributes()
    {
        return [
            'customer_name' => 'name',
            'customer_email' => 'email',
            'customer_mobile' => 'mobile'
        ];
    }
}
