<?php
namespace Lcg\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse{
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

    public static function BadRequest($message): JsonResponse{
        return self::Base(["message"=>$message], 400);
    }

    public static function Unauthorized($message): JsonResponse{
        return self::Base(["message"=>$message], 401);
    }

    public static function NotFound($message): JsonResponse{
        return self::Base(["message"=>$message], 404);
    }
}
