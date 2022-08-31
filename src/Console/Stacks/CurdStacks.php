<?php

namespace Lcg\Console\Stacks;

use Illuminate\Filesystem\Filesystem;
use Lcg\Console\Models\Table;
use Symfony\Component\Process\Process;

trait CurdStacks
{
    //生成视图文件
    protected function makeCurdViewStack(Table $model, bool $isCover=false){
        //获取内容
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/View.vue");

        //替换内容
        $content = str_replace("__MODEL_PK__", $model->primary_key->name, $content);
        $content = str_replace("__MODEL_NAME__", $model->model, $content);
        $content = str_replace("__FORM_ITEMS__", $model->getBladeFormContent(), $content);
        $content = str_replace("__VIEW_ITEMS__", $model->getBladeViewContent(), $content);
        $content = str_replace("__TABLE_ITEMS__", $model->getBladeTableContent(), $content);
        $content = str_replace("__SEARCH_ITEMS__", $model->getBladeSearchContent(), $content);

        //生成文件
        $filename = base_path("resources/js/Pages/{$model->model}.vue");
        if(!is_file($filename) || $isCover){
            //保存文件
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        } else {
            //显示记录
            $this->warn("[WARRING] Exist: $filename");
        }
    }

    //生成模型文件
    protected function makeCurdModelStack(Table $model, bool $isCover=false){
        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/Model.php");
        $content = str_replace("__MODEL_NAME__", $model->model, $content);
        $content = str_replace("/** MODEL_ANNOTATE */", $model->getModelAnnotateContent(), $content);
        $content = str_replace("/** MODEL_FIELDS */", $model->getModelFieldsContent(), $content);
        $content = str_replace("/** MODEL_LABELS */", $model->getModelLabelsContent(), $content);
        $content = str_replace("/** MODEL_RULES */", $model->getModelRulesContent(), $content);

        //生成文件
        $filename = base_path("app/Models/{$model->model}.php");
        if(!is_file($filename) || $isCover){
            //保存文件
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        } else {
            //显示记录
            $this->warn("[WARRING] Exist: $filename");
        }
    }

    //生成控制器文件
    protected function makeCurdControllerStack(Table $model, bool $isCover=false){
        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/Controller.php");
        $content = str_replace("__MODEL_NAME__", $model->model, $content);
        $content = str_replace("__MODEL_URL__", $model->url, $content);
        $content = str_replace("__MODEL_PK__", $model->primary_key->name, $content);
        $content = str_replace("__MODEL_PK_TYPE__", $model->primary_key->type, $content);
        $content = str_replace("__MODEL_SWAGGER_FIELDS__", $model->getSwaggerFieldsContent(), $content);

        //生成文件
        $filename = base_path("app/Http/Controllers/{$model->model}Controller.php");
        if(!is_file($filename) || $isCover){
            //保存文件
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        } else {
            //显示记录
            $this->warn("[WARRING] Exist: $filename");
        }
    }
}
