<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index()
    {
        $customers = Customer::with(['city', 'city.department'])->get();
        $available = 0;
        foreach ($customers as $customer) {
            $available = Credit::where('customer_id', $customer->id)->where('state', '==', 'active')->sum('due');
            $customer->available = $customer->credit_limit - $available;
        }

        return $customers;
    }

    public function store(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'document' => 'required | numeric | unique:customers',
                'name' => 'required | string | min:4',
                'lastname' => 'required | string | min:4',
                'address' => 'required | string',
                'phone' => 'required | string | min: 7',
                'contact' => 'required | string',
                'credit_limit' => 'required | numeric',
                'grace_days' => 'required | numeric | integer',
                'city_id' => 'required | numeric',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        };

        $customer = Customer::create([
            'document' => $request->document,
            'name' => $request->name,
            'lastname' => $request->lastname,
            'address' => $request->address,
            'phone' => $request->phone,
            'contact' => $request->contact,
            'credit_limit' => $request->credit_limit,
            'state' => 'active',
            'grace_days' => $request->grace_days,
            'city_id' => $request->city_id,
        ]);

        ActivityLogController::createActivityLog(
            'Se creó el cliente con id: ' . $customer->id,
            'create_customer');
    }

    public function show($id)
    {

        $clientExist = Customer::where('id', $id)->exists();

        if (!$clientExist) {
            return $this->errorResponse('El cliente que desea consultar no existe en la base de datos', 400);
        }

        $client = Customer::with(['city', 'city.department'])->find($id);

        $credits = Credit::where('customer_id', $client->id)->get();
        $available = 0;
        foreach ($credits as $value) {
            $available += $value->due;
        }
        $client->available =$client->credit_limit - $available;
        return $client;
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(), [
                'document' => 'required | numeric ',
                'name' => 'required | string | min:4',
                'lastname' => 'required | string | min:4',
                'address' => 'required | string',
                'phone' => 'required | string | min: 7',
                'contact' => 'required | string',
                'credit_limit' => 'required | numeric',
                'grace_days' => 'required | numeric | integer',
                'city_id' => 'required | numeric | integer | gt:0',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        };
        $clientEdit = Customer::findOrFail($id);
        $clientEdit->update([
            'document' => $request->document,
            'name' => $request->name,
            'lastname' => $request->lastname,
            'address' => $request->address,
            'phone' => $request->phone,
            'contact' => $request->contact,
            'credit_limit' => $request->credit_limit,
            'grace_days' => $request->grace_days,
            'city_id' => $request->city_id,
        ]);

        ActivityLogController::createActivityLog(
            'Se actualizo el cliente con id: ' . $clientEdit->id,
            'edit_customer');

    }

    public function destroy($id)
    {
        $customer = Customer::with('credits')->find($id);
        if (!$customer) {
            return $this->errorResponse('El cliente que desea eliminar no ha sido encontrado', 400);
        }

        $active_credits = Credit::where('customer_id', $id)->where('state', '!=', 'canceled')->count();
        if ($active_credits > 0) {
            return $this->errorResponse('El cliente no puede ser eliminado porque tiene créditos activos', 400);
        }
        $customer->delete();
    }
}
