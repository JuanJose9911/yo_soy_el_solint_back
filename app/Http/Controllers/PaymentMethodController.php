<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CreditController;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NumberFormatter;
use PDF;
use Carbon\Carbon;


class PaymentMethodController extends Controller
{
    public function findAll()
    {
        return response()->json(PaymentMethod::all());
    }

    public function findOne($id)
    {
        return response()->json(PaymentMethod::find($id));
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:payment_methods',
                'status' => 'boolean'
            ]);
            $paymentMethod = PaymentMethod::create([
                'name' => $request->name,
                'status' => $request->status
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($paymentMethod, 201);
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:payment_methods,name,' . $id,
                'status' => 'boolean'
            ]);
            $paymentMethod = PaymentMethod::find($id);
            $paymentMethod->update([
                'name' => $request->name,
                'status' => $request->status
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($paymentMethod, 200);
    }

    public function delete($id)
    {
        try {
            $paymentMethod = PaymentMethod::find($id);
            $paymentMethod->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Deleted'], 200);
    }

    private function arrayToDict($arr, $key)
    {
        $dict = [];
        foreach ($arr as $item) {
            $dict['n_' . $item[$key]] = $item;
        }
        return $dict;
    }

    public function GeneratePdfReceipt(Request $request)
    {
        $day = Carbon::now();
        $payment = Payment::with('credit.customer.city')->find($request->get('payment_id'));
        if ($payment->type_pay === 'capital'){
            $credit = $payment->credit;
            $customer = $credit->customer;
            $paid_monthly_fees = $credit->fees()->where('state', 'paid')->count();
            $monthly_fees_due = $credit->monthly_fees - $paid_monthly_fees;
            $spell_formater = new NumberFormatter("es", NumberFormatter::SPELLOUT);
            $spelled_total = str($spell_formater->format(floatval($payment->amount))) . ' PESOS';

            $data = [
                'day' => $day,//fecha actual
                'date' => $payment->date,//fecha de la factura
                'receipt' => $payment->id, //numero de recibo
                'cliente_support_number' => $payment->id, //numero de soporte de pago
                'client_id' => $customer->id, //id del cliente
                'client_name' => $customer->name . ' ' . $customer->lastname, //nombre del cliente
                'client_address' => $customer->address, //direccion del cliente
                'client_phone' => $customer->phone, //telefono del cliente
                'client_city' => $customer->city?->name, //ciudad del cliente
                'pay_number' => $credit->pagare_number, //numero de pago
                'create_date' => $payment->date, //fecha de creacion del pago
                'init_value' => $credit->initial_fee, //valor inicial del pago
                'agreed_term' => $customer->grace_days, //termino plazo pactado
                'annual_interest_rate' => $credit->interest_rate, //tasa de interes anual
                'outlay' => $credit->disbursement_date, //desembolso
                'capital_balance' => $credit->due, //saldo capital
                'outstanding_installment' => $paid_monthly_fees, //numero de cuotas pendientes
                'canceled_installment' => $monthly_fees_due, //numero de cuotas canceladas
                'amount' => $payment->amount, //numero de cuotas canceladas
                'spelled_total' => $spelled_total, //total en letras
            ];
            $pdf = PDF::loadView('payCapital', $data);
            $pdf->render();
            return $pdf->stream();
//            $this->pdfCapital($payment, $day);
        }
        if (!$payment) {
            return response('El pago seleccionado no ha sido encontrado', 404);
        }
        $credit = $payment->credit;
        $customer = $credit->customer;

        $paid_monthly_fees = $credit->fees()->where('state', 'paid')->count();
        $monthly_fees_due = $credit->monthly_fees - $paid_monthly_fees;
        $json = json_decode($payment->paid_fees, true);

        $temp = [];
        foreach ($json as $value) {
            if (array_key_exists($value['id'], $temp)) {
                $temp[$value['id']]['total_due_paid'] += array_key_exists('due_amount', $value) ? $value['due_amount'] : 0;
                $temp[$value['id']]['total_interest_paid'] += array_key_exists('interest_due_amount', $value) ? $value['interest_due_amount'] : 0;
                $temp[$value['id']]['total_late_paid'] += array_key_exists('late_due_amount', $value) ? $value['late_due_amount'] : 0;
                $temp[$value['id']]['number'] =  $value['number'];
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
        $spell_formater = new NumberFormatter("es", NumberFormatter::SPELLOUT);
        $spelled_total = str($spell_formater->format(floatval($payment->amount))) . ' PESOS';
        $data = [
            'day' => $day,//fecha actual
            'date' => $payment->date,//fecha de la factura
            'receipt' => $payment->id, //numero de recibo
            'cliente_support_number' => $payment->id, //numero de soporte de pago
            'client_id' => $customer->id, //id del cliente
            'client_name' => $customer->name . ' ' . $customer->lastname, //nombre del cliente
            'client_address' => $customer->address, //direccion del cliente
            'client_phone' => $customer->phone, //telefono del cliente
            'client_city' => $customer->city?->name, //ciudad del cliente
            'pay_number' => $credit->pagare_number, //numero de pago
            'create_date' => $payment->date, //fecha de creacion del pago
            'init_value' => $credit->initial_fee, //valor inicial del pago
            'agreed_term' => $customer->grace_days, //termino plazo pactado
            'annual_interest_rate' => $credit->interest_rate, //tasa de interes anual
            'outlay' => $credit->disbursement_date, //desembolso
            'capital_balance' => $credit->due, //saldo capital
            'outstanding_installment' => $paid_monthly_fees, //numero de cuotas pendientes
            'canceled_installment' => $monthly_fees_due, //numero de cuotas canceladas
            'fees' => $temp, //numero de cuotas canceladas
            'spelled_total' => $spelled_total, //total en letras
        ];
        $pdf = PDF::loadView('paySupport', $data);
        $pdf->render();
        return $pdf->stream();
    }

    public function GeneratePdfAmortization(Request $request)
    {
        $day = Carbon::now();
        $fees = CreditController::calculateCredit(
            $day,
            60,
            12.7,
            20000000
        );

        $pdf = PDF::loadView('amortizationTable', [
            'fees' => $fees,//array de cuotas
            'day' => $day,//fecha actual
            'creditNumber' => '12345',
            'numberFees' => count($fees),
            'disbursementDate' => '12/12/2018',
            'interestRate' => '12.70',
            'payNumber' => '12345',
            'fixAmount' => $fees[0]['cuota'],
        ]);

        $pdf->render();
        return $pdf->stream();
    }


}
