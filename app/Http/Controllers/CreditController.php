<?php

namespace App\Http\Controllers;

use App\Models\ConfigParameter;
use App\Models\Credit;
use App\Models\Customer;
use App\Models\Fee;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Claims\Custom;
use Carbon\Carbon;
use PDF;

class CreditController extends Controller
{

    public function recalculateFees($id)
    {
        $credit = Credit::findOrFail($id);
        $credit->loan_amount = $credit->amount - $credit->initial_fee;

        $new_fees = $this->calculateCredit(
            new Carbon($credit->date),
            $credit->monthly_fees,
            $credit->interest_rate,
            $credit->loan_amount,
        );

        $credit->fees()->forceDelete();

        $this->insertFeesIntoCredit($credit, $new_fees);

        $credit->due = $credit->loan_amount;
        $credit->save();
    }

    public function findAll()
    {
        return Credit::with('customer:id,name,lastname')->where('state', 'active')->orderBy('id', 'desc')->get();
    }

    public function findOne($id)
    {
        $credit = Credit::with(['customer', 'fees'])->findOrFail($id);
        foreach ($credit->fees as $key => $fee) {
            $fee->total_pay = $fee->due + $fee->interest_due + $fee->late_due;
        }
        return $credit;
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required',
                'pagare_number' => 'required | numeric',
                'amount' => 'required | numeric',
                'initial_fee' => 'required | numeric',
                'interest_rate' => 'required | numeric ',
                'monthly_fees' => 'required | numeric',
                'date' => 'required',
                'notes' => 'string',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($request->amount < 0) {
            return response()->json(['error' => 'El monto del credito es inválido'], 400);
        }
        if ($request->initial_fee >= $request->amount) {
            return response()->json(['error' => 'La cuota inicial no es válida'], 400);
        }

        try {
            DB::beginTransaction();
            $available = $this->calculateAvailable($request->customer_id);
            if ($available < $request->amount) {
                return response()->json(['error' => 'El monto del credito excede el cupo disponible'], 400);
            }

            $amount = $request->amount - $request->initial_fee;

            $credit = Credit::create([
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'pagare_number' => $request->pagare_number,
                'amount' => $request->amount,
                'loan_amount' => $amount,
                'initial_fee' => $request->initial_fee,
                'due' => $amount,
                'interest_rate' => $request->interest_rate,
                'monthly_fees' => $request->monthly_fees,
                'notes' => $request->notes,
                'disbursement_date' => $request->disbursement_date,
                'created_by' => Auth::id(),
            ]);

            $fees = $this->calculateCredit(
                new Carbon($request->date),
                $request->input('monthly_fees'),
                floatval($request->input('interest_rate')),
                intval($amount)
            );

            $this->insertFeesIntoCredit($credit, $fees);

            ActivityLogController::createActivityLog(
                'Se creó el crédito con id: ' . $credit->id,
                'create_credit');

            DB::commit();
            return response()->json($credit, 201);
        } catch (\Exception $err) {
            DB::rollBack();
            throw $err;
        }
    }

    public function insertFeesIntoCredit($credit, $fees)
    {
        foreach ($fees as $fee) {
            Fee::create([
                'number' => $fee['number'],
                'date' => $fee['date'],
                'amount' => $fee['amount'],
                'fee' => $fee['cuota'],
                'interest' => $fee['intereses'],
                'amortization' => $fee['amortizacion'],
                'credit_due' => $fee['deuda'],
                'due' => $fee['amortizacion'],
                'interest_due' => $fee['intereses'],
                'credit_id' => $credit->id,
                'created_by' => Auth::id()
            ]);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'notes' => 'required',
                'pagare_number' => 'required',
            ]);
            $credit = Credit::find($id);
            $credit->update([
                'notes' => $request->notes,
                'pagare_number' => $request->pagare_number,
                'date' => $request->date,
                'updated_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        return $credit;
    }

    public function delete($id)
    {
        $credit = Credit::find($id);
        $credit->delete();
    }

    public function deactivateCredit(Request $request, $id)
    {
        try {
            $request->validate([
                'inactivation_reason' => 'required|string'
            ]);
            $credit = Credit::find($id);
            $credit->update([
                'state' => 'canceled',
                'inactivation_reason' => $request->inactivation_reason
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        return $credit;
    }

    function simulateCredit(Request $request)
    {
        if ($request->amount < 0) {
            return response()->json(['error' => 'El monto del credito es inválido'], 400);
        }
        if ($request->initial >= $request->amount) {
            return response()->json(['error' => 'La cuota inicial no es válida'], 400);
        }
        $amount = $request->amount - $request->initial;
        $date = Carbon::now();
        return $this->calculateCredit(
            $date,
            $request->input('monthly_fees'),
            floatval($request->input('interest_rate')),
            intval($amount)
        );
    }

    public static function calculateCredit($date, $monthly_fees, $interest_rate_ea, $amount, $offset = 0, $advances = [], $capital1 = 0)
    {
        $fees = [];
        $deuda = $amount;
        if ($interest_rate_ea == 0) {
            $capital = $amount / $monthly_fees;
            $date->addMonths(1 + $offset);
            for ($i = 0; $i < $monthly_fees; $i++) {
                $fee = [];

                $amortizacion = $capital;
                foreach ($advances as $advance) {
                    if ($advance['number'] - 1 == $i) {
                        $amortizacion += $advance['amount'];
                    }
                }

                $number = $i + 1 + $offset;
                $date_s = $date->format('Y-m-d');
                $date->addMonths(1);

                $intereses = 0;

                if ($deuda < $amortizacion) {
                    //1. Recalcular la ultima cuota donde la deuda se hace negativa
                    $capital = $deuda + $intereses;
                    $amortizacion = $capital - $intereses;
                    $deuda -= $amortizacion;

                    //2. Guardar datos de la ultima cuota
                    $fee['number'] = $number;
                    $fee['date'] = $date_s;
                    $fee['amount'] = $amount;
                    $fee['cuota'] = round($capital);
                    $fee['intereses'] = round($intereses);

                    $fee['deuda'] = round($deuda);
                    $fee['amortizacion'] = round($amortizacion);
                    $fees[] = $fee;

                    //3. Romper el ciclo
                    break;
                }

                $deuda -= $amortizacion;

                $fee['number'] = $number;
                $fee['date'] = $date_s;
                $fee['amount'] = $amount;
                $fee['cuota'] = round($capital);
                $fee['intereses'] = $intereses;

                $fee['deuda'] = round($deuda);
                $fee['amortizacion'] = round($amortizacion);
                $fees[] = $fee;
            }
        } else {
            $interest_rate_m =  round((pow((1+($interest_rate_ea/100)), (1/12)) - 1),5); //($interest_rate_ea / 100) / 12;
            if ($capital1 > 0) {
                $capital = $capital1;
            } else {
                $capital = ($deuda * $interest_rate_m * (pow((1 + $interest_rate_m), ($monthly_fees)))) / ((pow((1 + $interest_rate_m), ($monthly_fees))) - 1);
            }
            $date->addMonths(1 + $offset);
            for ($i = 0; $i < $monthly_fees; $i++) {
                $fee = [];

                $amortizacion = ($capital - ($deuda * $interest_rate_m));

                foreach ($advances as $advance) {
                    if ($advance['number'] - 1 == $i) {
                        $amortizacion += $advance['amount'];
                    }
                }

                $number = $i + 1 + $offset;
                $date_s = $date->format('Y-m-d');
                $date->addMonths(1);

                $intereses = $deuda * $interest_rate_m;

                if ($deuda < $amortizacion) {
                    //1. Recalcular la ultima cuota donde la deuda se hace negativa
                    $capital = $deuda + $intereses;
                    $amortizacion = $capital - $intereses;
                    $deuda -= $amortizacion;

                    //2. Guardar datos de la ultima cuota
                    $fee['number'] = $number;
                    $fee['date'] = $date_s;
                    $fee['amount'] = $amount;
                    $fee['cuota'] = round($capital);
                    $fee['intereses'] = round($intereses);

                    $fee['deuda'] = round($deuda);
                    $fee['amortizacion'] = round($amortizacion);
                    $fees[] = $fee;

                    //3. Romper el ciclo
                    break;
                }
                $deuda -= $amortizacion;

                $fee['number'] = $number;
                $fee['date'] = $date_s;
                $fee['amount'] = $amount;
                $fee['cuota'] = round($capital);
                $fee['intereses'] = round($intereses);

                $fee['deuda'] = round($deuda);
                $fee['amortizacion'] = round($amortizacion);
                $fees[] = $fee;
            }
        }

        return $fees;
    }

    private function calculateAvailable(int $id)
    {
        $credits = Credit::where('customer_id', $id)->where('state', 'active')->get();
        $amount = 0;
        foreach ($credits as $value) {
            $amount += $value['due'];
        }
        $limit = Customer::where('id', $id)->first(['credit_limit']);
        return $limit->credit_limit - $amount;
    }

    public function generatePdfSimulation(Request $request)
    {
        $day = Carbon::now();
        $fees = $this->calculateCredit(
            $day,
            $request->query('monthly_fees'),
            $request->query('interest_rate'),
            floatval(str_replace(",", "", $request->query('amount'))) - floatval(str_replace(",", "", $request->query('initial_fee'))),
        );
        $pdf = PDF::loadView('simulationTable', [
            'initialFee' => $request->query('initial_fee'),
            'fees' => $fees,//array de cuotas
            'day' => $day,//fecha actual
            'numberFees' => $request->query('monthly_fees'),
            'interestRate' => $request->query('interest_rate'),
            'fixAmount' => $fees[0]['cuota'],
            'creditNumber' => '12345',
            'disbursementDate' => '12/12/2018',
            'payNumber' => '12345',
            'amount' => $request->query('amount'),
        ]);
        $pdf->render();

        return $pdf->stream();
    }

    private function parseState($state)
    {
        $dict = array(
            'paid' => 'Pagada',
            'in_due' => 'En mora',
            'to_pay' => 'Pendiente',
            'created' => ' ',
        );

        return $dict[$state];
    }

    public function reportCredit(Request $request)
    {
        $credit = Credit::with(['fees'])->find($request->query('credit_id'));

        $date = Carbon::now();

        foreach ($credit->fees as $fee) {
            $fee->state = $this->parseState($fee->state);
        }

        $pdf = PDF::loadView('amortizationTable', [
            'initialFee' => $credit->initial_fee,
            'fees' => $credit->fees,
            'day' => $date,
            'numberFees' => count($credit->fees),
            'interestRate' => $credit->interest_rate,
            'fixAmount' => $credit->fees[0]->fee,
            'creditNumber' => $credit->id,
            'disbursementDate' => $credit->disbursement_date,
            'payNumber' => $credit->pagare_number,
            'amount' => $credit->loan_amount,
        ]);

        $pdf->render();

        return $pdf->stream();
    }

    public function refinanceCredit(Request $request)
    {
        try {
            $request->validate([
                'credit_id' => 'required|exists:credits,id',
                'interest_rate' => 'required|numeric',
                'monthly_fees' => 'required|numeric',
                'date' => 'required'
            ]);
            $credit = Credit::find($request->credit_id);
            $pending_fees = $credit->fees()->whereIn('state', ['in_due', 'to_pay'])->get();
            $amount = $credit->due;
            DB::beginTransaction();
            foreach ($pending_fees as $fee) {
                $amount += $fee->due;
                $amount += $fee->interest_due;
                $amount += $fee->late_due;
            }
            $credit->fees()->forceDelete();
            $credit->due = $amount;
            $credit->loan_amount = $amount;
            $credit->interest_rate = $request->interest_rate;
            $credit->save();
            $date = new Carbon($request->date);
            $fees = $this->calculateCredit($date, $request->monthly_fees, $request->interest_rate, $amount);

            $this->insertFeesIntoCredit($credit, $fees);
            ActivityLogController::createActivityLog(
                'Se refinancio el crédito con id: ' . $credit->id,
                'refinance_credit');

            DB::commit();
            return $fees;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }

    }

    public function extractoPago(Request $request)
    {
        //1. Consultar el credito, las cuotas por pagar y el ultimo pago
        $credit = Credit::with('customer.city')->find($request->query('credit_id'));
        $fee_to_pay = $credit->fees()->whereIn('state', ['in_due', 'to_pay'])->orderBy('number', 'desc')->first();
        if (is_null($fee_to_pay)) {
            return $this->errorResponse('NO HAY CUOTAS PENDIENTES POR PAGAR', 400);
        }
        $last_pay = Payment::where('credit_id', $request->query('credit_id'))->where('type_pay', 'fee')->orderBy('id', 'desc')->first();

        $temp = [];
        $last_capital = 0;
        $last_interest = 0;
        $last_mora = 0;
        $last_total = 0;

        if (is_null($last_pay)) {
            $pay = [
                'total_due_paid' => 0,
                'total_interest_paid' => 0,
                'total_late_paid' => 0,
            ];
            $temp[] = $pay;
            $last_date = ' ';
        }else{
            $json = json_decode($last_pay->paid_fees, true);

            foreach ($json as $value) {
                if (array_key_exists($value['id'], $temp)) {
                    $temp[$value['id']]['total_due_paid'] += array_key_exists('due_amount', $value) ? $value['due_amount'] : 0;
                    $temp[$value['id']]['total_interest_paid'] += array_key_exists('interest_due_amount', $value) ? $value['interest_due_amount'] : 0;
                    $temp[$value['id']]['total_late_paid'] += array_key_exists('late_due_amount', $value) ? $value['late_due_amount'] : 0;
                    $temp[$value['id']]['number'] = $value['number'];
                } else {
                    $temp[$value['id']] = [
                        'total_due_paid' => array_key_exists('due_amount', $value) ? $value['due_amount'] : 0,
                        'total_interest_paid' => array_key_exists('interest_due_amount', $value) ? $value['interest_due_amount'] : 0,
                        'total_late_paid' => array_key_exists('late_due_amount', $value) ? $value['late_due_amount'] : 0,
                        'number' => $value['number'],
                    ];
                }
            }
            sort($temp);
            array_multisort(array_column($temp, 'number'), SORT_ASC, $temp);
            $last_date = $last_pay->date;
        }
        foreach ($temp as $value) {
            $last_capital += $value['total_due_paid'];
            $last_interest += $value['total_interest_paid'];
            $last_mora += $value['total_late_paid'];
        }

        $last_total = $last_capital + $last_interest + $last_mora;

        $paid_fees = $credit->fees()->where('state', 'paid')->count();
        $pendant_fees = $credit->monthly_fees - $paid_fees;
        $day = Carbon::now()->format('Y-m-d');
        $pay_date = new Carbon($fee_to_pay->date);
        $pay_date->addDays(5);
        $total_pay = $fee_to_pay->due + $fee_to_pay->interest_due + $fee_to_pay->late_due;


        $data = [
            'credit_id' => $credit->id,
            'day' => $day,//fecha actual
            'client_id' => $credit->customer->id, //id del cliente
            'client_name' => $credit->customer->name,//nombre del cliente
            'client_address' => $credit->customer->address, //direccion del cliente
            'client_phone' => $credit->customer->phone, //telefono del cliente
            'client_city' => $credit->customer->city->name, //ciudad del cliente
            'date_cut' => $fee_to_pay->date, //fecha de corte
            'pay_date' => $pay_date->format('Y-m-d'), //fecha de vencimiento
            'pay_total' => $total_pay, //total a pagar
            'pay_status' => $this->parseState($fee_to_pay->state), //estado del pago
            'pay_number' => $credit->pagare_number, //numero de pago
            'create_date' => $credit->date, //fecha de creacion del pago
            'init_value' => $credit->initial_fee, //valor inicial del pago
            'agreed_term' => $credit->customer->grace_days, //termino plazo pactado
            'annual_interest_rate' => $credit->interest_rate, //tasa de interes anual
            'outlay' => $credit->disbursement_date, //desembolso loan amount
            'capital_balance' => $credit->due, //saldo capital
            'outstanding_installment' => $paid_fees, //numero de cuotas pendientes
            'canceled_installment' => $pendant_fees, //numero de cuotas canceladas
            'last_capital' => $last_capital, //$temp[0]['total_due_paid'], //saldo capital anterior
            'last_interest' => $last_interest, //$temp[0]['total_interest_paid'], //intereses anuales anteriores
            'last_mora' => $last_mora,//$temp[0]['total_late_paid'], //mora anteriores
            'last_total' => $last_total,//$temp[0]['total_due_paid'] + $temp[0]['total_interest_paid'] + $temp[0]['total_late_paid'], //total anteriores
            'actual_capital' => $fee_to_pay->due, //saldo capital actual
            'actual_interest' => $fee_to_pay->interest_due, //intereses anuales actuales
            'actual_mora' => $fee_to_pay->late_due, //mora actuales
            'actual_total' => $fee_to_pay->due + $fee_to_pay->interest_due + $fee_to_pay->late_due, //total actuales
            'last_pay_date' => $last_date, //fecha de ultimo pago
        ];

        $pdf = PDF::loadView('extractTable', $data);
        $pdf->render();
        return $pdf->stream();
    }
}
