<?php

namespace App\Console\Commands;

use App\Lib\GstPlaceToPay;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActualizarEPendientes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:pendientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza las ordenes que quedaron en estado pendiente por algun error' .
    'de comunicacion con el webservice de placetopay';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GstPlaceToPay $gstPlaceToPay)
    {
        $ordenes = Order::where('status', 'PENDING')->get();
        $estadosActualizados = '';
        $cases = '';
        $param1 = [];

        foreach ($ordenes as $orden) {
            //realizo una consulta en ptp
            $estado = $gstPlaceToPay->getStatusPago($orden->payment->request_id)->status();
            $estado = ($estado != 'APPROVED') ?: 'PAYED';

            if ($orden->status != $estado) {
                $cases .= "WHEN {$orden->id} then ? ";
                $param1[] = $estado;
                $estadosActualizados .= $orden->id . ',';
            }
        }
        if ($estadosActualizados) {
            $estadosActualizados = trim($estadosActualizados, ',');

            $res = \DB::update("UPDATE `{$ordenes[0]->getTable()}` SET `status` = CASE `id` {$cases} END
            WHERE `id` in (" . $estadosActualizados . ")", $param1);

            if ($res) {
                Log::info('Id de ordenes actualizadas: ' . $estadosActualizados);
            } else {
                Log::error('Se produjo un error al actualizar las ordenes: ' . $estadosActualizados);
            }
        }
    }
}
