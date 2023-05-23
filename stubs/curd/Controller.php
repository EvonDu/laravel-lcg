<?php

namespace __CONTROLLER_NAMESPACE__;

/** CONTROLLER_USES */
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Expand\Lcg\Http\Responses\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Tag(name="__MODEL_NAME__", description="__MODEL_NAME__")
 */
class __CONTROLLER_NAME__ extends Controller
{
    /**
     * VIEW - INDEX
     * @return mixed
     */
    public function page(){
        $labels = __MODEL_NAME__::labels();
        return Inertia::render('__VIEW_PATH__', [
            "api" => url("/__BASE_URL__/interface"),
            "labels" => $labels,
            "breadcrumbs" => [
                [ "title" => "Home", "url" => url("/") ]
            ],
        ]);
    }

    /**
     * API - LIST
     * @OA\Get(
     *      path="/api/__BASE_URL__",
     *      tags={"__MODEL_NAME__"},
     *      summary="List",
     *      description="List",
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function index(Request $request){
        $search = __MODEL_NAME__::search($request);
        $paginate = __MODEL_NAME__::paginate($search, $request);
        return Response::OK([
            "paginate" => $paginate,
            "data" => $search->get(),
        ]);
    }

    /**
     * API - CREATE
     * @OA\Post(
     *      path="/api/__BASE_URL__",
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
        return Response::OK(["data" => $model]);
    }

    /**
     * API - SHOW
     * @OA\Get(
     *      path="/api/__BASE_URL__/{__MODEL_PK__}",
     *      tags={"__MODEL_NAME__"},
     *      summary="GET",
     *      description="GET",
     *      @OA\Parameter(name="__MODEL_PK__", required=true, in="path", description="__MODEL_PK__", @OA\Schema(type="__MODEL_PK_TYPE__")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function show(Request $request, $id){
        //Find
        $model = __MODEL_NAME__::findOne($id);
        if($model == null)
            throw new NotFoundHttpException("Not Found");

        //Return
        return Response::OK(["data" => $model]);
    }

    /**
     * API - UPDATE
     * @OA\Put(
     *      path="/api/__BASE_URL__/{__MODEL_PK__}",
     *      tags={"__MODEL_NAME__"},
     *      summary="UPDATE",
     *      description="UPDATE",
     *      @OA\Parameter(name="__MODEL_PK__", required=true, in="path", description="__MODEL_PK__", @OA\Schema(type="__MODEL_PK_TYPE__")),
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
        $model = __MODEL_NAME__::findOne($id);
        if($model == null)
            throw new NotFoundHttpException("Not Found");

        //Load
        $model->loadParams($request->input());
        $model->save();

        //Return
        return Response::OK(["data" => $model]);
    }

    /**
     * API - DELETE
     * @OA\Delete(
     *      path="/api/__BASE_URL__/{__MODEL_PK__}",
     *      tags={"__MODEL_NAME__"},
     *      summary="DELETE",
     *      description="DELETE",
     *      @OA\Parameter(name="__MODEL_PK__", required=true, in="path", description="__MODEL_PK__", @OA\Schema(type="__MODEL_PK_TYPE__")),
     *      @OA\Response(response="default", description="result")
     * )
     */
    public function destroy(Request $request, $id){
        //Find
        $model = __MODEL_NAME__::findOne($id);
        if($model == null)
            throw new NotFoundHttpException("Not Found");

        //Delete
        $model->delete();

        //Return
        return Response::OK(["message" => "OK"]);
    }
}
