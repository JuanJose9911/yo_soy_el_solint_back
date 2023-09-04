<?php

namespace App\Http\Controllers;

use App\Models\ConfigParameter;
use App\Models\Credit;
use App\Models\Customer;
use App\Models\Fee;
use Illuminate\Http\Request;

class ConfigController extends Controller
{

    public function store(Request $request)
    {
        $param = ConfigParameter::findOrFail($request->key);
        $param->value = $request->value;
        $param->save();

        ActivityLogController::createActivityLog(
            'Ha cambiado el valor de la configuración ' . $param->key . ' a ' . $param->value,
            'update_config_param');

    }

    public function home()
    {
        $credits_qty = Credit::count();
        $loan_amount = Credit::sum('loan_amount');
        $due_amount = Credit::sum('due');
        $fees_qty = Fee::whereIn('state', ['to_pay', 'in_due'])->count();
        $customers_qty = Customer::count();
        return [
            'credits_qty' => $credits_qty,
            'loan_amount' => $loan_amount,
            'due_amount' => $due_amount,
            'fees_qty' => $fees_qty,
            'customers_qty' => $customers_qty,
        ];
    }

    function update(Request $request)
    {
        $parameter = ConfigParameter::find($request->key);
        $parameter->value = $request->value;
        $parameter->save();
        return response()->json(['success' => true]);
    }

    function get(Request $request)
    {
        return ConfigParameter::all();
    }
}
