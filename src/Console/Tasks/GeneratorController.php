<?php
namespace Lcg\Console\Tasks;

use Illuminate\Console\View\Components\Factory;
use Lcg\Console\Traits\Codes;
use Lcg\Models\Curd;
use Lcg\Models\Table;

class GeneratorController{
    /**
     * 引入特征
     */
    Use Codes;

    /**
     * 执行生成
     *
     * @param Factory $factory
     * @param Table $table
     * @param Curd $curd
     * @param array $options
     * @return void
     */
    public static function run(Factory $factory, Table $table, Curd $curd, array $options = []){
        //引入列表
        $use_list = [ "use {$curd->getModelClassname()};" ];
        if($curd->getPath()){
            $use_list[] = "use App\\Http\\Controllers\\Controller;";
        }
        $controller_uses = implode("\n", $use_list);

        //视图路径
        $view_path = "{$curd->getViewPath()}";
        if(isset($options["style"]) && $options["style"] == 2)
            $view_path = "$view_path/Index";

        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/Controller.php");

        //组装模板
        $content = str_replace("__CONTROLLER_NAME__", $curd->getControllerName(), $content);
        $content = str_replace("__CONTROLLER_NAMESPACE__", $curd->getControllerNamespace(), $content);
        $content = str_replace("/** CONTROLLER_USES */", $controller_uses, $content);
        $content = str_replace("__MODEL_NAME__", $curd->getModelName(), $content);
        $content = str_replace("__MODEL_PK__", $table->primary_key->name, $content);
        $content = str_replace("__MODEL_PK_TYPE__", $table->primary_key->type, $content);
        $content = str_replace("__MODEL_SWAGGER_FIELDS__", self::getSwaggerFieldsContent($table), $content);
        $content = str_replace("__BASE_URL__", $curd->getUrl(), $content);
        $content = str_replace("__VIEW_PATH__", $view_path, $content);

        //接口模式
        if(isset($options["type"]) && $options["type"] == "api"){
            //删除视图相关方法
            $content = preg_replace("/\s*\/\*{2}\s*\* VIEW - ([\S|\s]*?)\}\s{2}/", "", $content);
        }

        //生成文件
        $cover = isset($options["cover"]) ? $options["cover"] : false;
        self::put($factory, base_path("app/Http/Controllers/{$curd->getPath()}/{$curd->getControllerName()}.php"), $content, $cover);
    }

    /**
     * 获取Swagger字段描述
     *
     * @param Table $table
     * @return string
     */
    private static function getSwaggerFieldsContent(Table $table){
        $contents = [];
        $examples = [];
        foreach ($table->fields as $field){
            if(in_array($field->name, ["created_at", "updated_at", $table->primary_key->name]))
                continue;
            $contents[] = "     *              @OA\Property(description=\"{$field->name}\", property=\"{$field->name}\", type=\"{$field->type}\"),";
            $examples[$field->name] = "";
        }
        $contents[] = "     *              example=" . json_encode($examples);
        $contents[0] = str_replace("     *              ", "", $contents[0]);
        return implode("\n", $contents);
    }
}
