<?php

namespace App\Console\Commands;

use App\Models\ConfigParameter;
use App\Models\Fee;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-fees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcula el pago de intereses por mora';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now();
        Log::debug('olis');
        $fees = Fee::with([
            'credit:id,customer_id',
            'credit.customer:id,grace_days'
        ])->where('date', '<=', $today->format('Y-m-d'))
            ->where('state', '!=', 'paid')
            ->get();
        foreach ($fees as $value) {
            $grace_days = $value->credit->customer->grace_days;
            $date_diff = $today->diffInDays($value['date']);
            //Para calcular el valor de la mora
            if ($date_diff >= $grace_days) {
                $late_interest_rate = ConfigParameter::where('key', 'late_interest_rate')->first()->value;
                $value->late_due = intval((($value->due + $value->interest_due) * (floatval($late_interest_rate) / 100) / 360) * $date_diff);
                $value->late_interest_pay = intval((($value->due + $value->interest_due) * (floatval($late_interest_rate) / 100) / 360) * $date_diff);
                $value->late_interest_rate = $late_interest_rate;
                $value->state = 'in_due';
                $value->save();
            }
            //Para cambiar el estado de la cuota
            if ($value['date'] == $today->format('Y-m-d')) {
                $value->state = 'to_pay';
                $value->due = $value['amortization'];
                $value->interest_due = $value['interest'];
                $value->save();
            }
        }
    }
}
