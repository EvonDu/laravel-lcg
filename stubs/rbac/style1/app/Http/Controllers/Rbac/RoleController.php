<?php

namespace App\Http\Controllers\Rbac;

use App\Models\Rbac\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Lcg\Http\Responses\ApiResponse;

/**
 * @OA\Tag(name="Role", description="Role")
 */
class RoleController extends Controller
{
    /**
     * VIEW - INDEX
     * @return mixed
     */
    public function page(){
        $labels = Role::labels();
        return Inertia::render('Rbac/Role', [
            "api" => url("/rbac/roles/interface"),
            "labels" => $labels,
            "permissions" => config("permissions"),
            "breadcrumbs" => [
                [ "title" => "Home", "url" => url("/") ]
            ],
        ]);
    }

    /**
     * API - LIST
     * @OA\Get(
     *      path="/api/rbac/roles",
     *      tags={"Role"},
     *      summary="List",
     *      description="List",
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function index(Request $request){
        $search = Role::search($request);
        $paginate = Role::paginate($search, $request);
        return ApiResponse::OK([
            "paginate" => $paginate,
            "data" => $search->get(),
        ]);
    }

    /**
     * API - CREATE
     * @OA\Post(
     *      path="/api/rbac/roles",
     *      tags={"Role"},
     *      summary="CREATE",
     *      description="CREATE",
     *      @OA\RequestBody(required=true, @OA\MediaType(
     *          mediaType="application/json", @OA\Schema(
     *              @OA\Property(description="name", property="name", type="string"),
     *              @OA\Property(description="permissions", property="permissions", type="json"),
     *              example={"name":"","permissions":""}
     *          )
     *      )),
     *      @OA\Response(response="default", description="result"),
     * )
     */
    public function store(Request $request){
        Role::validate($request);
        $model = new Role();
        $model->loadParams($request->input());
        $model->save();
        return ApiResponse::OK(["data" => $model]);
    }

    /**
     * API - SHOW
     * @OA\Get(
     *      path="/api/rbac/roles/{id}",
     *      tags={"Role"},
     *      summary="GET",
     *      description="GET",
     *      @OA\Parameter(name="id", required=true, in="path", description="id", @OA\Schema(type="integer")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function show(Request $request, $id){
        //Find
        $model = Role::findOne($id);
        if($model == null)
            return ApiResponse::NotFound("Not Found");

        //Return
        return ApiResponse::OK(["data" => $model]);
    }

    /**
     * API - UPDATE
     * @OA\Put(
     *      path="/api/rbac/roles/{id}",
     *      tags={"Role"},
     *      summary="UPDATE",
     *      description="UPDATE",
     *      @OA\Parameter(name="id", required=true, in="path", description="id", @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\MediaType(
     *          mediaType="application/json", @OA\Schema(
     *              @OA\Property(description="name", property="name", type="string"),
     *              @OA\Property(description="permissions", property="permissions", type="json"),
     *              example={"name":"","permissions":""}
     *          )
     *      )),
     *      @OA\Response(response="default", description="result"),
     * )
     */
    public function update(Request $request, $id){
        //Validate
        Role::validate($request);

        //Find
        $model = Role::findOne($id);
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
     *      path="/api/rbac/roles/{id}",
     *      tags={"Role"},
     *      summary="DELETE",
     *      description="DELETE",
     *      @OA\Parameter(name="id", required=true, in="path", description="id", @OA\Schema(type="integer")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function destroy(Request $request, $id){
        //Find
        $model = Role::findOne($id);
        if($model == null)
            return ApiResponse::NotFound("Not Found");

        //Delete
        $model->delete();

        //Return
        return ApiResponse::OK(["message" => "OK"]);
    }
}
