<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use Carbon\Carbon;


trait ApiResponserTrait
{
    /**
     * susccesResponse
     *
     * @param  string $data
     * @param  int  $code
     * @return Illuminate\Http\JsonResponse
     */
    public function susccesResponse($data, $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json(['data' => $data], $code);
    }
    /**
     * errorResponse
     *
     * @param  string $message
     * @param  int $code
     * @return Illuminate\Http\JsonResponse
     */
    public function errorResponse($message, $code): JsonResponse
    {
        return response()->json(['error' => $message], $code);
    }
}
