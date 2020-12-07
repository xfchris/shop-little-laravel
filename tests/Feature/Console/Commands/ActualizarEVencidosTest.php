<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\ActualizarEVencidos;
use App\Models\Order;
use Dnetix\Redirection\Entities\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActualizarEVencidosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function en_tareas_programadas_verificar_que_cambia_a_rechazado_ordenes_vencidas()
    {
        $fechaVencida = date('Y-m-d H:i:s', strtotime('-' . (config('constants.dias_expiracion')) . ' days '.
            ' -3 seconds'));
        $fechasVencidas = ['created_at' => $fechaVencida, 'updated_at' => $fechaVencida];
        //creo 2 ordenes vencidas y una no vencida
        $orden1 = Order::factory()->create($fechasVencidas);
        $orden2 = Order::factory()->create($fechasVencidas);
        $orden3 = Order::factory()->create();

        //ejecuto comando
        $comando = new ActualizarEVencidos();
        $comando->handle();

        //verifico que el estado sea vencido
        $ordenesVencidas = Order::whereIn('id',[$orden1->id, $orden2->id, $orden3->id])->where('status', Status::ST_REJECTED)->count();
        $this->assertSame($ordenesVencidas, 2);
    }
}
