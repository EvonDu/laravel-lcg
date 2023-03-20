<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\Server;
use OpenApi\Generator;

class SwaggerServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        //非调试模式时,取消警告信息(防止输出的文档错误)
        if(!isset($_GET["debug"])){
            error_reporting(E_ERROR);
            ini_set("display_errors","Off");
        }

        //全部接口文档
        Route::get('/swagger/all', function () {
            //获取注解openapi
            $openapi = $this->getOpenApiGenerator();
            //返回结果(JSON形式)
            return response()->json($openapi->jsonSerialize());
        });

        //有效接口文档
        Route::get('/swagger/api', function () {
            //获取注解openapi
            $openapi = $this->getOpenApiGenerator();

            //获取应用routes
            $routes = $this->getApplicationRoutes();

            //遍历处理所有路径下的所有方法
            $tags_exist = [];
            foreach ($openapi->paths as $item){
                $none = "@OA\Generator::UNDEFINED🙈";
                foreach (["get", "put", "post", "delete", "options", "head", "patch", "trace"] as $method){
                    $url = preg_replace("/\{.*?\}/", "{*}", $item->{$method}->path);
                    if(!is_string($item->{$method})){
                        //过滤未配置路由的接口文档
                        if(!in_array($url, $routes[strtoupper($method)])){
                            //删除该路径方法的接口信息
                            $item->{$method} = $none;
                            continue;
                        }

                        //记录有效的标签
                        foreach ($item->{$method}->tags as $tag){
                            if(!in_array($tag, $tags_exist))
                                $tags_exist[] = $tag;
                        }
                    }
                }
            }

            //过滤没有相关接口的标签
            $tags = [];
            foreach ($openapi->tags as $tag) {
                if(in_array($tag->name, $tags_exist)){
                    $tags[] = $tag;
                }
            }
            $openapi->tags = $tags;

            //返回结果(JSON形式)
            return response()->json($openapi->jsonSerialize());
        });
    }

    /**
     * 获取注解OpenApi
     *
     * @return \OpenApi\Annotations\OpenApi|null
     */
    public function getOpenApiGenerator(){
        //扫描文件相关注解
        $openapi = Generator::scan([
            app_path(),
        ]);

        //设置相关文档属性
        $openapi->info = new Info([
            "title" => env('SWAGGER_INFO_TITLE', env('APP_NAME', 'Laravel')." - API Documentation"),
            "version" => env('SWAGGER_INFO_VERSION', null),
            "description" => env('SWAGGER_INFO_VERSION', null),
        ]);

        //设置接口服务属性
        $openapi->servers = [
            new Server([
                "description" => "default",
                "url" => url("/"),
            ]),
        ];

        //返回结果
        return $openapi;
    }

    /**
     * 获取应用Routes
     *
     * @return array|array[]
     */
    public function getApplicationRoutes(){
        $result = [
            "GET" => [],
            "PUT" => [],
            "POST" => [],
            "DELETE" => [],
            "OPTIONS" => [],
            "HEAD" => [],
            "PATCH" => [],
            "TRACE" => [],
        ];

        $routes  = Route::getRoutes();
        foreach ($routes as $route) {
            if (!empty($route->action["as"])) {
                $url = "/" . preg_replace("/\{.*?\}/", "{*}", $route->uri);
                $result[$route->methods[0]][] = $url;
            }
        }

        return $result;
    }
}
