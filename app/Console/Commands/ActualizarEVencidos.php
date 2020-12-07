<?php

namespace App\Console\Commands;

use App\Models\Order;
use Dnetix\Redirection\Entities\Status;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActualizarEVencidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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
    public function handle()
    {
        //Se obtienen pagos que las fechas de vencimiento ayan pasado y
        //se cambia el estado a vencido.
        $updated = Order::where('created_at', '<', date('c', strtotime('-'.config('constants.dias_expiracion').' days')))
            ->where('status', 'CREATED')->update(['status' => Status::ST_REJECTED]);
        if ($updated){
            Log::info('Ordenes vencidas actualizadas.');
        }
    }
}
