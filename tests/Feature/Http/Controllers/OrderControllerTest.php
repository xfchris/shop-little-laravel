<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\OrderController;
use App\Http\Requests\OrdenRequest;
use App\Lib\GstPlaceToPay;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Dnetix\Redirection\Entities\Status;
use Dnetix\Redirection\Message\RedirectInformation;
use Dnetix\Redirection\Message\RedirectResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $gstPlaceToPay;
    protected $ordenController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gstPlaceToPay = $this->createMock(GstPlaceToPay::class);
        $this->ordenController = new OrderController();
    }

    /**
     * @test
     */
    public function verificar_que_los_no_logeados_no_entran_a_ordenes()
    {
        $res = $this->get(route('home'));
        $res->assertStatus(302);
        $res->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function verificar_lista_de_ordenes()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $res = $this->actingAs($user)->get(route('home'));
        $res->assertStatus(200);
        $res->assertSee($order->customer_name);
    }

    /**
     * Verifica que al mandar un request con datos incorrectos,
     * muestre error de validacion
     *
     * @test
     */
    public function en_buscar_orden_comprobar_error_al_mandar_datos_incorrectos()
    {
        $data = [
            'customer_nombre' => 'Christian Valencia',
            'customer_email' => 'emailIncorrecto',
            'customer_mobile' => '333222'
        ];
        $res = $this->post(route('buscarOrden'), $data);
        $res->assertStatus(422);
        $res->assertSee('error');
    }


    /**
     * Verifica que al mandar un request con datos incorrectos en inciarPago,
     * muestre error de validacion
     *
     * @test
     */
    public function en_iniciar_pago_comprobar_error_al_mandar_datos_incorrectos()
    {
        $data = [
            'customer_name' => 'Christian Valencia',
            'customer_email' => 'emailIncorrecto',
            'customer_mobile' => '333222'
        ];
        $res = $this->post(route('iniciarPago'), $data);
        $res->assertStatus(422);
        $res->assertSee('error');
    }


    /**
     * Verifica que al mandar un request con datos correctos pero que si existe la orden
     * en inciarPago, muestre error de validacion
     *
     * @test
     */
    public function en_iniciar_pago_comprobar_error_al_existir_orden()
    {
        $payment = Payment::factory()->create();
        $data = [
            'customer_name' => $payment->order->customer_name,
            'customer_email' => $payment->order->customer_email,
            'customer_mobile' => $payment->order->customer_mobile,
        ];
        $res = $this->post(route('iniciarPago'), $data);
        $res->assertStatus(400);
        $res->assertSee('Ya existe un registro en base de datos con los datos de esta orden');
    }

    /**
     * Verifica que un pago que esta en estado iniciado, al pasar por aceptar pago, cambia de
     * estado a pagado
     *
     * @test
     */
    public function en_aceptar_pago_verificar_que_cambia_el_estado_a_pagado_y_redirecciona()
    {
        //creo orden en estado iniciado
        $payment = Payment::factory()->create();
        //el metodo getStatusPago me retorna que esta pagada
        $this->gstPlaceToPay->method('getStatusPago')
            ->willReturn($this->returnRedirectInformacion(Status::ST_APPROVED)->status());

        $res = $this->ordenController->aceptarPago($this->gstPlaceToPay, $payment->order_id);

        //compruebo que haya cambiado en base de datos el estado del pago
        $this->assertSame($payment->order->status, Config('constants.status.PAYED'));
        //compruebo que redirecciona
        $this->assertSame($res->status(), 302);
    }

    /**
     * Verifica que un pago que esta en estado iniciado, al pasar por aceptar pago, cambia de
     * estado a pagado
     *
     * @test
     */
    public function en_cancelar_pago_verificar_que_cambia_el_estado_a_rechazado_y_redirecciona()
    {
        //creo orden en estado iniciado
        $payment = Payment::factory()->create();
        //el metodo getStatusPago me retorna que esta pendiente
        $this->gstPlaceToPay->method('getStatusPago')
            ->willReturn($this->returnRedirectInformacion(Status::ST_REJECTED)->status());

        $res = $this->ordenController->cancelarPago($this->gstPlaceToPay, $payment->order_id);

        //compruebo que haya cambiado en base de datos el estado del pago
        $this->assertSame($payment->order->status, Config('constants.status.REJECTED'));
        //compruebo que redirecciona
        $this->assertSame($res->status(), 302);
    }

    /**
     * Verifica que al mandar un request con datos correctos, y la orden exista, la devuelva,
     *
     * @test
     */
    public function en_buscar_orden_comprobar_que_encuentra_la_orden()
    {
        $this->gstPlaceToPay->method('getStatusPago')
            ->willReturn($this->returnRedirectInformacion()->status());
        $payment = Payment::factory()->create();

        //Creo request
        $data = [
            'customer_name' => $payment->order->customer_name,
            'customer_email' => $payment->order->customer_email,
            'customer_mobile' => $payment->order->customer_mobile
        ];
        //Creo request
        $request = $this->createRequest(route('buscarOrden'), $data);
        //Busco  orden
        $res = $this->ordenController->buscarOrden($request, $payment->order, $this->gstPlaceToPay);
        //Compruebo que retorna la orden
        $this->assertSame($payment->process_url, $res->getData()->data->payment->process_url);
    }

    /**
     * @test
     */
    public function en_inciar_pago_comprobar_que_inicia_el_pago_al_tener_datos_correctos()
    {
        $this->gstPlaceToPay->method('pagar')->willReturn($this->returnRedirectResponse());
        $data = [
            'customer_name' => 'Jhon Perez',
            'customer_email' => 'test@test.com',
            'customer_mobile' => '333222'
        ];
        //Creo request
        $request = $this->createRequest(route('iniciarPago'), $data);
        //Inciar pago
        $res = $this->ordenController->iniciarPago($request, new Order(), $this->gstPlaceToPay);

        //Compruebo el pago iniciado en base de datos
        $pago = Payment::where('process_url', $res->getData()->data->url)->first();
        $this->assertNotNull($pago);
    }

    private function returnRedirectResponse(){
        return new RedirectResponse([
            'requestId' => 123456,
            'processUrl' => 'https://test.placetopay.com/redirection/session/123456/un_hash_largisimo'
        ]);
    }

    /**
     * Retorna lo que debería devolver la funcion query de la libreria ptp
     *
     * @param string $status
     * @return RedirectInformation
     */
    private function returnRedirectInformacion($status = 'CREATED')
    {
        return new RedirectInformation([
            "requestId" => 12345,
            "status" => [
                "status" => $status,
                "reason" => 200,
                "message" => 'CCC',
                "date" => "2020-11-30T12:52:51-05:00",
            ]
        ]);
    }

    /**
     * Crea una peticion
     *
     * @return OrdenRequest
     */
    private function createRequest($route, $data)
    {
        return OrdenRequest::create($route,'POST', $data);
    }
}
