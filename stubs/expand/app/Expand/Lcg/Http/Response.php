<?php

namespace App\Expand\Lcg\Http;

use Illuminate\Http\JsonResponse;

class Response{
    private static function Base($options=[], $code=200): JsonResponse{
        $result = ["code" => $code];
        foreach ($options as $name=>$option){
            $result[$name] = $option;
        }
        return response()->json($result, $code);
    }

    public static function OK($options=[], $code=200): JsonResponse{
        return self::Base($options, $code);
    }

    public static function Error($message, $code=500): JsonResponse{
        return self::Base(["message"=>$message], $code);
    }
}
