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
    protected $signature = 'orders:u_pendientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $estadosActualizados = [];
        foreach ($ordenes as $orden) {
            //realizo una consulta en ptp
            $estado = $gstPlaceToPay->getStatusPago($orden->payment->request_id);
            if ($orden->status != Config('constants.status.' . $estado->status())) {
                $orden->status = ($estado->status() != 'APPROVED') ?: 'PAYED';
                $orden->save();
                $estadosActualizados[] = $orden->id;
            }
        }
        if (count($estadosActualizados)){
            Log::info('Id de ordenes actualizadas: '.json_encode($estadosActualizados));
        }
    }
}
