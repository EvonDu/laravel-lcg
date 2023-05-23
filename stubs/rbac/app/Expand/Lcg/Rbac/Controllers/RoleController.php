<?php

namespace App\Expand\Lcg\Rbac\Controllers;

use App\Expand\Lcg\Http\Responses\Response;
use App\Expand\Lcg\Rbac\Models\Role;
use App\Expand\Lcg\Rbac\Models\RoleUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        return Inertia::render('__VIEW_PATH__', [
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
        return Response::OK([
            "paginate" => $paginate,
            "data" => $search->get(),
        ]);
    }

    /**
     * API - CREATE
     * @OA\Post(
     *      path="/api/rbac/roles",
     *      tags={"Role"},
     *      summary="Create",
     *      description="Create",
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
        return Response::OK(["data" => $model]);
    }

    /**
     * API - SHOW
     * @OA\Get(
     *      path="/api/rbac/roles/{id}",
     *      tags={"Role"},
     *      summary="Detail",
     *      description="Detail",
     *      @OA\Parameter(name="id", required=true, in="path", description="id", @OA\Schema(type="integer")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function show(Request $request, $id){
        //Find
        $model = Role::findOne($id);
        if($model == null)
            throw new NotFoundHttpException("Not Found");

        //Return
        return Response::OK(["data" => $model]);
    }

    /**
     * API - UPDATE
     * @OA\Put(
     *      path="/api/rbac/roles/{id}",
     *      tags={"Role"},
     *      summary="Update",
     *      description="Update",
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
            throw new NotFoundHttpException("Not Found");

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
     *      summary="Delete",
     *      description="Delete",
     *      @OA\Parameter(name="id", required=true, in="path", description="id", @OA\Schema(type="integer")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function destroy(Request $request, $id){
        //Find
        $model = Role::findOne($id);
        if($model == null)
            throw new NotFoundHttpException("Not Found");

        //Delete
        $model->delete();

        //Return
        return Response::OK(["message" => "OK"]);
    }

    /**
     * API - SEARCH USERS
     * @OA\Get(
     *      path="/api/rbac/roles/{id}/search",
     *      tags={"Role"},
     *      summary="Search users",
     *      description="Search users",
     *      @OA\Parameter(name="id", required=true, in="path", description="role_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="k", required=true, in="query", description="keyword", @OA\Schema(type="integer")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function userSearch(Request $request){
        $key = $request->input("k", "");
        $list = User::where('email', 'LIKE', "%{$key}%")->get(["name", "email"]);
        return Response::OK(["data" => $list,]);
    }

    /**
     * API - ROLE USER LIST
     * @OA\Get(
     *      path="/api/rbac/roles/{id}/users",
     *      tags={"Role"},
     *      summary="Role user list",
     *      description="Role user list",
     *      @OA\Parameter(name="id", required=true, in="path", description="id", @OA\Schema(type="integer")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function userList(Request $request, $id){
        //Find
        $model = Role::findOne($id);
        if($model == null)
            throw new NotFoundHttpException("Not Found");

        //Return
        return Response::OK(["data" => $model->roleUsers]);
    }

    /**
     * API - ROLE USER PUSH
     * @OA\Post (
     *      path="/api/rbac/roles/{id}/users",
     *      tags={"Role"},
     *      summary="Role user remove",
     *      description="Role user remove",
     *      @OA\Parameter(name="id", required=true, in="path", description="role_id", @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\MediaType(
     *          mediaType="application/json", @OA\Schema(
     *              @OA\Property(description="name", property="name", type="string"),
     *              example={"email":""}
     *          )
     *      )),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function userPush(Request $request, $id){
        //Find
        $email = $request->input("email", "");
        $user = User::where('email', $email)->first();
        if($user == null)
            throw new NotFoundHttpException("Not Found");

        //PUSH
        $model = new RoleUser();
        $model->role_id = $id;
        $model->user_id = $user->id;
        $model->save();

        //Return
        return Response::OK(["message" => "OK"]);
    }

    /**
     * API - ROLE USER REMOVE
     * @OA\Delete(
     *      path="/api/rbac/roles/{id}/users/{user_id}",
     *      tags={"Role"},
     *      summary="Role user remove",
     *      description="Role user remove",
     *      @OA\Parameter(name="id", required=true, in="path", description="role_id", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="user_id", required=true, in="path", description="user_id", @OA\Schema(type="integer")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function userRemove(Request $request, $id, $user_id){
        //Find
        $model = RoleUser::findOne(["role_id" => $id, "user_id" => $user_id]);
        if($model == null)
            throw new NotFoundHttpException("Not Found");

        //Delete
        $model->delete();

        //Return
        return Response::OK(["message" => "OK"]);
    }
}
