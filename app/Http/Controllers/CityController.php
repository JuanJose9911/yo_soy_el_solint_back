<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        //Listar ciudades
        $cities = City::get([
            'id',
            'name'
        ]);
        return response()->json([
            'success' => true,
            'cities' => $cities
        ],201);
    }
}
