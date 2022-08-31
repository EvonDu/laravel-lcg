<?php

namespace App\Http\Controllers;

use App\Models\__MODEL_NAME__;
use Illuminate\Http\Request;
use Lcg\Exceptions\Inertia;
use Lcg\Exceptions\ApiResponse;

/**
 * @OA\Tag(name="__MODEL_NAME__",description="__MODEL_NAME__")
 */
class __MODEL_NAME__Controller extends Controller
{
    /**
     * VIEW - INDEX
     * @return mixed
     */
    public function page(){
        $labels = __MODEL_NAME__::labels();
        return Inertia::render('__MODEL_NAME__', [
            "api" => url("/api/__MODEL_URL__"),
            "labels" => $labels,
            "breadcrumbs" => [
                [ "title" => "Home", "url" => url("/") ]
            ],
        ]);
    }

    /**
     * API - LIST
     * @OA\Get(
     *      path="/api/__MODEL_URL__",
     *      tags={"__MODEL_NAME__"},
     *      summary="List",
     *      description="List",
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function index(Request $request){
        $search = __MODEL_NAME__::search($request);
        $paginate = __MODEL_NAME__::paginate($search, $request);
        return ApiResponse::OK([
            "paginate" => $paginate,
            "data" => $search->get(),
        ]);
    }

    /**
     * API - CREATE
     * @OA\Post(
     *      path="/api/__MODEL_URL__",
     *      tags={"__MODEL_NAME__"},
     *      summary="CREATE",
     *      description="CREATE",
     *      @OA\RequestBody(required=true, @OA\MediaType(
     *          mediaType="application/json", @OA\Schema(
     *              __MODEL_SWAGGER_FIELDS__
     *          )
     *      )),
     *      @OA\Response(response="default", description="result"),
     * )
     */
    public function store(Request $request){
        __MODEL_NAME__::validate($request);
        $model = new __MODEL_NAME__();
        $model->loadParams($request->input());
        $model->save();
        return ApiResponse::OK(["data" => $model]);
    }

    /**
     * API - SHOW
     * @OA\Get(
     *      path="/api/__MODEL_URL__/{__MODEL_PK__}",
     *      tags={"__MODEL_NAME__"},
     *      summary="GET",
     *      description="GET",
     *      @OA\Parameter(name="__MODEL_PK__", required=true, in="path",description="__MODEL_PK__", @OA\Schema(type="__MODEL_PK_TYPE__")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function show(Request $request, $id){
        //Find
        $model = __MODEL_NAME__::query()->find($id);
        if($model == null)
            return ApiResponse::NotFound("Not Found");

        //Return
        return ApiResponse::OK(["data" => $model]);
    }

    /**
     * API - UPDATE
     * @OA\Put(
     *      path="/api/__MODEL_URL__/{__MODEL_PK__}",
     *      tags={"__MODEL_NAME__"},
     *      summary="UPDATE",
     *      description="UPDATE",
     *      @OA\Parameter(name="__MODEL_PK__", required=true, in="path",description="__MODEL_PK__", @OA\Schema(type="__MODEL_PK_TYPE__")),
     *      @OA\RequestBody(required=true, @OA\MediaType(
     *          mediaType="application/json", @OA\Schema(
     *              __MODEL_SWAGGER_FIELDS__
     *          )
     *      )),
     *      @OA\Response(response="default", description="result"),
     * )
     */
    public function update(Request $request, $id){
        //Validate
        __MODEL_NAME__::validate($request);

        //Find
        $model = __MODEL_NAME__::query()->find($id);
        if($model == null)
            return ApiResponse::NotFound("Not Found");

        //Load
        $model->loadParams($request->input());
        $model->save();

        //Return
        return $model;
    }

    /**
     * API - DELETE
     * @OA\Delete(
     *      path="/api/__MODEL_URL__/{__MODEL_PK__}",
     *      tags={"__MODEL_NAME__"},
     *      summary="DELETE",
     *      description="DELETE",
     *      @OA\Parameter(name="__MODEL_PK__", required=true, in="path",description="__MODEL_PK__", @OA\Schema(type="__MODEL_PK_TYPE__")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function destroy(Request $request, $id){
        //Find
        $model = __MODEL_NAME__::query()->find($id);
        if($model == null)
            return ApiResponse::NotFound("Not Found");

        //Delete
        $model->delete();

        //Return
        return ApiResponse::OK(["message" => "OK"]);
    }
}