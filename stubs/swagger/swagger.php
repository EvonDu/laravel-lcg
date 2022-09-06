<?php
//非调试模式时,取消警告信息(防止输出的文档错误)
if(!isset($_GET["debug"])){
    error_reporting(E_ERROR);
    ini_set("display_errors","Off");
}

//加载依赖
require __DIR__.'/../vendor/autoload.php';

//扫描文件相关注解
$openapi = \OpenApi\Generator::scan([
    __DIR__ . "/../app/",
]);

//设置相关文档属性
$openapi->info = new \OpenApi\Annotations\Info([
    "title" => env('SWAGGER_INFO_TITLE', env('APP_NAME', 'Laravel')." - API Documentation"),
    "version" => env('SWAGGER_INFO_VERSION', null),
    "description" => env('SWAGGER_INFO_VERSION', null),
]);

//设置接口服务属性
$openapi->servers = [
    new \OpenApi\Annotations\Server([
        "description" => "default",
        "url" => dirname($_SERVER["PHP_SELF"]),
    ]),
];

//输出结果
header('Content-Type: application/x-yaml');
echo $openapi->toYaml();
