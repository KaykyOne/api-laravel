<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\MessageBag;

trait HttpResponses
{
    public function response(string $message, array|Model|JsonResource $data = [] , int $status = 200){
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => $status
        ], $status);
    }

    public function error(string $message, array|MessageBag $errors = [], int $status = 422){
        return response()->json([
            'message' => $message,
            'errors' => $errors,
            'data' => [],
            'status' => $status
        ], $status);
    }
}
