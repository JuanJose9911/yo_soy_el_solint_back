<?php

namespace App\Http\Controllers;

use App\Models\InterestRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InterestRateController extends Controller
{
    public function index()
    {
        return InterestRate::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
                'percent' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        };
        $interestRate = InterestRate::create([
            'percent' => $request->percent,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tasaInteres = InterestRate::findOrFail($id);

        $validator = Validator::make(
            $request->all(), [
                'percent' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        };

        $interestRate = $tasaInteres->update([
            'percent' => $request->percent,
            'updated_by' => Auth::id()
        ]);
    }

    public function destroy($id)
    {
        $interestRateDelete = InterestRate::findOrFail($id);
        $interestRateDelete->delete();
    }
}
