<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\ActualizarEPendientes;
use App\Lib\GstPlaceToPay;
use App\Models\Order;
use App\Models\Payment;
use Dnetix\Redirection\Entities\Status;
use Dnetix\Redirection\Message\RedirectInformation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ActualizarEPendientesTest extends TestCase
{
    use RefreshDatabase;
    protected $gstPlaceToPay;
    protected $ordenController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gstPlaceToPay = $this->createMock(GstPlaceToPay::class);
    }

    /**
     * @test
     */
    public function en_tarea_programada_verificar_que_cambia_estados_a_ordenes_pendientes(){
        //falseo servicio placetopay para que ponga las ordenes en estado pendientes
        $this->gstPlaceToPay->method('getStatusPago')
            ->willReturn($this->returnRedirectInformacion(Status::ST_APPROVED)->status());

        //Se crea 2 ordenes en estado Pendiente
        $payment1 = Payment::factory()->create();
        $payment1->order->store(['status'=>Status::ST_PENDING]);
        $payment2 = Payment::factory()->create();
        $payment2->order->store(['status'=>Status::ST_PENDING]);

        $aEPendientes = new ActualizarEPendientes();
        $aEPendientes->handle($this->gstPlaceToPay);

        //Compruebo que las ordenes creadas esten en estado pagada
        $ordenesPagadas = Order::whereIn('id',[$payment1->id, $payment2->id])->where('status', 'PAYED')->count();
        $this->assertSame($ordenesPagadas, 2);
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
}
