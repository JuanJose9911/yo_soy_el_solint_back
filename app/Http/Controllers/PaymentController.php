<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ConfigParameter;
use App\Models\Credit;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;
use stdClass;

class PaymentController extends Controller
{

    public function simulateCapital(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric',
                'interest_rate' => 'required|numeric',
                'credit_amount' => 'required|numeric',
                'initial_fee' => 'required|numeric',
                'monthly_fees' => 'required|numeric',
                'type' => 'required|string',
                'fees' => 'required|array',
            ]);

            // Validar que el abono no sea mayor que el saldo
            if ($request->amount > $request->credit_amount) {
                return $this->errorResponse('No se puede realizar el pago, el monto a pagar excede el valor de la deuda', 400);
            }
            $credit = [
                'amount' => $request->credit_amount,
                'interest_rate' => $request->interest_rate,
                'initial_fee' => $request->initial_fee,
                'monthly_fees' => $request->monthly_fees,
                'type' => $request->type,
                'fees' => $request->fees,
            ];

            if ($request->type == 'baja_valor_cuota') {
                return $this->simulateBajarValor($request->amount, $credit);
            } else if ($request->type == 'baja_tiempo') {
                return $this->simulateBajarTiempo($request->amount, $credit);
            } else {
                return $this->errorResponse('Tipo de pago inválido', 400);
            }

        }catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function simulateBajarTiempo($amount, $credit){
        if(count($credit['fees']) == 0){
            return $this->errorResponse('No se puede realizar el pago, no hay cuotas pendientes', 400);
        }

        $number = $credit['fees'][0]['number'];

        $advances[] = [
            'number' => $number,
            'amount' => $amount,
        ];

        $cuota = $credit['fees'][0]['cuota'];
        Log::debug($cuota);
        $date = Carbon::parse($credit['fees'][0]['date']);
        return CreditController::calculateCredit($date, $credit['monthly_fees'], $credit['interest_rate'], $credit['amount'], 0, $advances, $cuota);

    }

    static function simulateBajarValor($amount, $credit){
        $fees = $credit['fees'];
        $amount1 = $credit['amount'] - $amount - $fees[0]['amortizacion'];

        $date = Carbon::parse($fees[0]['date']);
        return CreditController::calculateCredit($date, $credit['monthly_fees'], $credit['interest_rate'], $amount1, 0);
    }

    public function capital(Request $request)
    {

        // 1. Validar campos
        try {
            $request->validate([
                'amount' => 'required| numeric | gt:1999999',
                'credit_id' => 'required | exists:credits,id',
                'date' => 'required',
            ]);


            $credit = Credit::find($request->credit_id);

            // 2. Validar cuotas vencidas o pendientes por pagar
            $due_fees = $credit->fees()->whereIn('state', ['in_due', 'to_pay'])->where('date', '<=', $request->date)->count();
            if ($due_fees > 0) {
                return $this->errorResponse('No se puede realizar el abono existen cuotas pendientes por pagar', 400);
            }
            //validar que no pueda pagar mas del monto total actual
            if ($request->amount > $credit->due) {
                return $this->errorResponse('No se puede realizar el pago, el monto a pagar excede el valor de la deuda', 400);
            }

            DB::beginTransaction();

            // 3. Procesar segun el tipo
            if ($request->type == 'baja_valor_cuota') {
                [$new_fees, $old_fees, $offset] = $this->bajaValorCuota($request->amount, $credit);
//                return $this->bajaValorCuota($request->amount, $credit);
            } else if ($request->type == 'baja_tiempo') {
                [$new_fees, $old_fees, $offset] = $this->bajarTiempo($request->amount, $credit);
//                return $this->bajarTiempo($request->amount, $credit);
            } else {
                return $this->errorResponse('Tipo de pago inválido', 400);
            }

            $feesx = $credit->fees()->get();
            $new_fees_dict = $this->arrayToDict($new_fees, 'number');

            foreach ($feesx as $feex) {
                $k = 'n_' . $feex->number;
                if (!array_key_exists($k, $new_fees_dict)) {
                    $feex->delete();
                    continue;
                }
                $fee = $new_fees_dict[$k];
                if ($feex->state == 'paid') {
                    continue;
                }
                $feex->date = $fee['date'];
                $feex->amount = $fee['amount'];
                $feex->fee = $fee['cuota'];
                $feex->interest = $fee['intereses'];
                $feex->amortization = $fee['amortizacion'];
                $feex->credit_due = $fee['deuda'];
                $feex->due = $fee['amortizacion'];
                $feex->interest_due = $fee['intereses'];
                $feex->created_by = 1;
                $feex->save();
            }

            $credit->due = $old_fees->first()->credit_due - $request->amount;
            $credit->save();
            // TODO: registrat el pago en la tabla
            // 4. Agregar el registro del pago

            $payment = Payment::create([
                'amount' => $request->amount,
                'type_pay' => 'capital',
                'date' => $request->date,
                'credit_id' => $request->credit_id,
                'created_by' => Auth::id()
            ]);

            DB::commit();
            return [$new_fees, $old_fees];
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function arrayToDict($arr, $key)
    {
        $dict = [];
        foreach ($arr as $item) {
            $dict['n_' . $item[$key]] = $item;
        }
        return $dict;
    }

    private function bajarTiempo($amount, $credit)
    {
        // 1. Calcular nuevo numero de cuotas
        $created_fees = $credit->fees()->where('state', 'paid')->orderBy('id', 'desc')->get();
        if (count($created_fees) == 0) {
            //No hay ninguna cuota paga
            $created_fees = $credit->fees()->where('state', 'created')->get();
        }
        $number = $created_fees->first()->number;
        // 2. Recalcular la tabla
        $advances = [];
        $advances[] = [
            'number' => $number,
            'amount' => $amount,
        ];
        $date = new Carbon($credit->date);
        $new_fees = CreditController::calculateCredit($date, $credit->monthly_fees, $credit->interest_rate, $credit->loan_amount, 0, $advances, $created_fees->first()->fee);

        ActivityLogController::createActivityLog(
            'Se amortizo el credito con id: ' . $credit->id . ' por el valor de ' . $amount,
            'reudcir_cuotas_credito');

        return [$new_fees, $created_fees, 0];
    }

    private function bajaValorCuota($amount, $credit)
    {
        // 1. Selecionar las cuotas que bajarán de valor
        $fees = $credit->fees()->where('state', '!=', 'paid')->get();

        $last_paid_fee = $credit->fees()->where('state', '=', 'paid')->orderBy('id', 'desc')->get();
        if (is_null($last_paid_fee->first())) {
            //No hay ninguna cuota paga
            $last_paid_fee = $credit->fees()->where('state', '=', 'created')->get();
            $amount1 = $credit->due - $amount;
            $offset = 0;
        } else {
            $offset = $last_paid_fee->first()->number;
            if (count($last_paid_fee) > 1) {
                // Hay mas de UNA cuota paga
                $amount1 = $last_paid_fee[1]->credit_due - $amount - $last_paid_fee->first()->amortization;
            } else {
                // Solo hay UNA cuota paga
                $amount1 = $credit->due - $amount - $last_paid_fee->first()->amortization;
            }
        }
        $monthly_fees = count($fees);
        $date = new Carbon($credit->date);
        $new_fees = CreditController::calculateCredit($date, $monthly_fees, $credit->interest_rate, $amount1, $offset);
        ActivityLogController::createActivityLog(
            'Se amortizo el credito con id: ' . $credit->id . ' por el valor de ' . $amount,
            'reducir_valor_cuotas_credito');
        return [$new_fees, $last_paid_fee, $offset];
    }

    public function registerPayment(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required| numeric | gt:1000',
                'date' => 'required',
                'credit_id' => 'required| exists:credits,id',
            ]);

            DB::beginTransaction();
            $total_pay = $request->amount;
            $credit = Credit::with('customer')->find($request->credit_id);
            $fees = $credit->fees()->where('state', '!=', 'paid')->orderBy('number', 'asc')->get();


            // Verificar los intereses por mora
            $grace_days = $credit->customer->grace_days;
            $late_interest_rate = ConfigParameter::where('key', 'late_interest_rate')->first()->value;
            $request_date = new Carbon($request->date);
            foreach ($fees as $fee) {
                $fee_date = new Carbon($fee->date);
                if ($fee_date > Carbon::now()) {
                    continue;
                }
                if ($fee_date > $request_date) {
                    //No hay dias de mora
                    $fee->late_due = 0;
                    continue;
                }
                $date_fee = new Carbon($fee->date);
                $diff_in_days = $date_fee->diffInDays($request->date);
                $late_days = $diff_in_days - $grace_days;
                if ($late_days > 0) {
                    $late_interest = intval((($fee->due + $fee->interest_due) * (floatval($late_interest_rate) / 100) / 360) * $diff_in_days);
                    $fee->late_due = $late_interest;
                } else {
                    // No hay dias de mora
                    $fee->late_due = 0;
                }
//                $fee->save();
            }
            // Pagar las cuotas
            [$paid_due, $paid_fees] = $this->payFees($fees, $total_pay);
            $payment = Payment::create([
                'amount' => $request->amount,
                'type_pay' => 'fee',
                'date' => $request->date,
                'paid_fees' => json_encode($paid_fees),
                'credit_id' => $request->credit_id,
                'created_by' => Auth::id()
            ]);
            $credit->due -= $paid_due;
            $credit->save();
            ActivityLogController::createActivityLog(
                'Se registro el pago con id: ' . $payment->id . ' para el credito con id: ' . $credit->id,
                'regist_payment');
            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }

    }

    function payRows($total_pay2, $fees)
    {
        $paid_due = 0;
        $paid_fees = [];
        $total_pay = $total_pay2;
        foreach ($fees as $fee) {
            if ($total_pay <= 0) {
                break;
            }
            if ($total_pay >= $fee->interest_due) {
                $total_pay -= $fee->interest_due;
                if ($fee->interest_due > 0) {
                    $paid_fees[] = [
                        'id' => $fee->id,
                        'interest_due_amount' => $fee->interest_due,
                        'number' => $fee->number,
                        'type' => 'total_interest_due',
                    ];
                }
                $fee->interest_due = 0;
                $fee->save();
                if ($total_pay >= $fee->due) {
                    // Pagos completos
                    $total_pay -= $fee->due;
                    $paid_due += $fee->due;
                    $paid_fees[] = [
                        'id' => $fee->id,
                        'due_amount' => $fee->due,
                        'number' => $fee->number,
                        'type' => 'total_due',
                    ];
                    $fee->due = 0;
                    $fee->state = 'paid';

                    $fee->save();
                    if ($total_pay > 0) continue; else break;
                } else {
                    // Pagos de cuotas parciales
                    $paid_due += $total_pay;
                    $fee->due -= $total_pay;
                    $paid_fees[] = [
                        'id' => $fee->id,
                        'due_amount' => $total_pay,
                        'number' => $fee->number,
                        'type' => 'partial_due',
                    ];
                    $total_pay = 0;
                    $fee->save();
                }
            } else {
                $fee->interest_due -= $total_pay;
                $paid_fees[] = [
                    'id' => $fee->id,
                    'interest_due_amount' => $total_pay,
                    'number' => $fee->number,
                    'type' => 'partial_interest_due',
                ];
                $total_pay = 0;
                $fee->save();
            }
        }

        return [$paid_due, $paid_fees];
    }

    function payColumn($column, $paid_column, $fees, $total_pay2)
    {
        $total_pay = $total_pay2;
        $paid_fees = [];
        foreach ($fees as $i => $fee) {
            if ($total_pay >= $fee[$column]) {
                $total_pay -= $fee[$column];
                if ($fee[$column] > 0) {
                    $paid_fees[] = [
                        'id' => $fee->id,
                        'late_due_amount' => $fee[$column],
                        'number' => $fee->number,
                        'type' => 'total_late_due',
                    ];
                }
                $fee[$paid_column] += $fee[$column];
                $fee[$column] = 0;
                $fee->save();
            } else {
                $fee[$column] -= $total_pay;
                $paid_fees[] = [
                    'id' => $fee->id,
                    'late_due_amount' => $total_pay,
                    'number' => $fee->number,
                    'type' => 'partial_late_due',
                ];
                $fee[$paid_column] += $total_pay;
                $total_pay = 0;
                $fee->save();
                break;
            }
        }

        return [$total_pay, $paid_fees];
    }

    private function payFees($fees, $total_pay1)
    {
        $total_pay = $total_pay1;
        $paid_fees = [];

        // 1. Pagos de Mora
        [$total_pay, $paid_columns] = $this->payColumn('late_due', 'late_interest_paid', $fees, $total_pay);

        if (!$total_pay) {
            return [$total_pay, $paid_columns];
        }

        // 2. Pagos de Cuotas
        [$paid_due, $paid_rows] = $this->payRows($total_pay, $fees); // returns $paid_due, $paid_fees
        $paid_fees = array_merge($paid_columns, $paid_rows);
        return [$paid_due, $paid_fees];
    }

    public function totalPayment(Request $request)
    {
        try {
            $request->validate([
                'credit_id' => 'required| exists:credits,id'
            ]);
            $credit = Credit::with('customer')->find($request->credit_id);
            $fees = Fee::where('credit_id', $request->credit_id)
                ->whereIn('state', ['in_due', 'to_pay'])
                ->where('date', '<=', $request->date)
                ->get();
            //return $fees;
            $total = 0;
            $due = 0;
            $interest_due = 0;
            $late_due = 0;
            $grace_days = $credit->customer->grace_days;
            $late_interest_rate = ConfigParameter::where('key', 'late_interest_rate')->first()->value;

            foreach ($fees as $fee) {
                $date_fee = new Carbon($fee->date);
                $request_date = new Carbon($request->date);
                $diff_in_days = $date_fee->diffInDays($request_date);
                $late_days = $diff_in_days - $grace_days;
                if ($late_days > 0) {
                    $late_interest = intval((($fee->due + $fee->interest_due) * (floatval($late_interest_rate) / 100) / 360) * $diff_in_days);
                    $late_due += $late_interest;
                }
                $due += $fee->due;
                $interest_due += $fee->interest_due;
            }
            $total = $due + $interest_due + $late_due;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        return [
            'total' => $total,
            'due' => $due,
            'interest_due' => $interest_due,
            'late_due' => $late_due
        ];
    }

    public function historicalPay(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required',
                'credit_id' => 'required | exists:fees,credit_id',
            ]);
            //1. Listar las cuotas a pagar
            $fees = Fee::where('credit_id', $request->credit_id)->get();
            [$total_pay, $paid_columns] = $this->payColumn('late_due', 'late_interest_paid', $fees, $request->amount);
            [$paid_due, $paid_fees] = $this->payRows($total_pay, $fees);
            $credit = Credit::where('id', $request->credit_id)->first();
            $credit->due -= $paid_due;
            $credit->save();
            return $paid_due;

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function addLateDue(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required',
                'credit_id' => 'required | exists:fees,credit_id'
            ]);
            $today = Carbon::now()->format('Y-m-d');
            $fees = Fee::where('date', '<=', $today)->where('state', '!=', 'paid')->where('credit_id', $request->credit_id)->get();
            foreach ($fees as $fee) {
                $fee->state = 'in_due';
                $fee->save();
            }
            $fees[count($fees) - 1]->late_due += $request->amount;
            $fees[count($fees) - 1]->late_interest_pay += $request->amount;
            $fees[count($fees) - 1]->save();
            return $fees;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function paymentReceipt(int $id)
    {
        $payment = Payment::with([
            'credit:id,customer_id,disbursement_date,interest_rate,pagare_number,monthly_fees,credit_number',
            'credit.customer:id,document,name,address,contact,city_id',
            'credit.customer.city'
        ])->find($id);
        $payments = Payment::with([
            'credit:id,customer_id,disbursement_date,interest_rate,pagare_number,monthly_fees,credit_number',
            'credit.customer:id,document,name,address,contact,city_id',
            'credit.customer.city'
        ])->where('credit_id', $payment->credit->id)->get();
        //calcular cuotas canceladas - preguntar por cuotas pagadas - agregarle campos a payment como el ejemplo ola
        $payment->cuotasCanceladas = count($payments);
        $payment->cuotasPendientes = $payment->credit->monthly_fees - $payment->cuotasCanceladas;
        $payment->fechaActual = Carbon::now();
        //falta el calculo a capital e intereses
        return $payment;
    }

    public function getPaid()
    {
        $paids = Payment::with(['credit', 'credit.customer', 'credit.customer.city'])->orderBy('id', 'desc')->get();
        return $paids;
    }

    public function otroGet()
    {
        $paids = Payment::orderBy('created_at', 'desc')->with([
            'credit:id,customer_id,disbursement_date,interest_rate,pagare_number,monthly_fees,credit_number',
            'credit.customer:id,document,name,address,contact,city_id',
            'credit.customer.city'
        ])->get();
        return $paids;
    }
}
