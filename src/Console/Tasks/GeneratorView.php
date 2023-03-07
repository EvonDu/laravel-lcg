<?php
namespace Lcg\Console\Tasks;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Lcg\Utils\CodeUtil;
use Lcg\Utils\CurdUtil;
use Lcg\Utils\TableUtil;

class GeneratorView{
    /**
     * 执行生成
     *
     * @param Command $command
     * @param TableUtil $table
     * @param CurdUtil $mvc
     * @param int $style
     * @param bool $isCover
     * @return void
     */
    public static function run(Command $command, TableUtil $table, CurdUtil $mvc, int $style, bool $isCover=false){
        switch ($style){
            case 2:
                self::generatorStyle2($command, $table, $mvc, $isCover);
                break;
            case 1:
                self::generatorStyle1($command, $table, $mvc, $isCover);
                break;
        }
    }

    /**
     * 生成视图文件(单文件)
     *
     * @param Command $command
     * @param TableUtil $table
     * @param CurdUtil $mvc
     * @param bool $isCover
     * @return void
     */
    private static function generatorStyle1(Command $command, TableUtil $table, CurdUtil $mvc, bool $isCover=false){
        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/style1/View.vue");
        $content = str_replace("__MODEL_PK__", $table->primary_key->name, $content);
        $content = str_replace("__MODEL_NAME__", $mvc->getModelName(), $content);
        $content = str_replace("__TABLE_ITEMS__", self::getViewTableContent($table), $content);
        $content = str_replace("__FORM_ITEMS__", self::getViewFormContent($table, "slot", 5), $content);
        $content = str_replace("__DETAIL_ITEMS__", self::getViewDetailContent($table, "slot", 5), $content);
        $content = str_replace("__SEARCH_ITEMS__", self::getViewSearchContent($table, "slot", 5), $content);

        //生成文件
        self::addFile($command, base_path("resources/js/Pages/{$mvc->getPath()}/{$mvc->getModelName()}.vue"), $content, $isCover);
    }

    /**
     * 生成视图文件(多文件)
     *
     * @param Command $command
     * @param TableUtil $table
     * @param CurdUtil $mvc
     * @param bool $isCover
     * @return void
     */
    private static function generatorStyle2(Command $command, TableUtil $table, CurdUtil $mvc, bool $isCover=false){
        //Index
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/style2/Views/Index.vue");
        $content = str_replace("__MODEL_NAME__", $mvc->getModelName(), $content);
        $content = str_replace("__MODEL_PK__", $table->primary_key->name, $content);
        $content = str_replace("__TABLE_ITEMS__", self::getViewTableContent($table), $content);
        self::addFile($command, base_path("resources/js/Pages/{$mvc->getPath()}/{$mvc->getModelName()}/Index.vue"), $content, $isCover);

        //Search
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/style2/Views/Search.vue");
        $content = str_replace("__SEARCH_ITEMS__", self::getViewSearchContent($table, "data", 3), $content);
        self::addFile($command, base_path("resources/js/Pages/{$mvc->getPath()}/{$mvc->getModelName()}/Search.vue"), $content, $isCover);

        //Create
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/style2/Views/Create.vue");
        $content = str_replace("__FORM_ITEMS__", self::getViewFormContent($table, "data", 3), $content);
        self::addFile($command, base_path("resources/js/Pages/{$mvc->getPath()}/{$mvc->getModelName()}/Create.vue"), $content, $isCover);

        //Update
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/style2/Views/Update.vue");
        $content = str_replace("__MODEL_PK__", $table->primary_key->name, $content);
        $content = str_replace("__FORM_ITEMS__", self::getViewFormContent($table, "data", 3), $content);
        self::addFile($command,base_path("resources/js/Pages/{$mvc->getPath()}/{$mvc->getModelName()}/Update.vue"), $content, $isCover);

        //Detail
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/style2/Views/Detail.vue");
        $content = str_replace("__DETAIL_ITEMS__", self::getViewDetailContent($table, "data", 3), $content);
        self::addFile($command,base_path("resources/js/Pages/{$mvc->getPath()}/{$mvc->getModelName()}/Detail.vue"), $content, $isCover);
    }

    /**
     * 添加生成文件
     *
     * @param Command $command
     * @param string $filename
     * @param string $content
     * @param bool $isCover
     * @return void
     */
    private static function addFile(Command $command, string $filename, string $content, bool $isCover){
        if(!is_file($filename) || $isCover){
            //保存文件
            (new Filesystem)->ensureDirectoryExists(dirname($filename));
            file_put_contents($filename, $content);
            //显示记录
            $command->info("[APPEND] $filename");
        }
        else {
            //显示记录
            $command->warn("[WARRING] Exist: $filename");
        }
    }

    /**
     * 获取模板表格内容
     *
     * @param TableUtil $table
     * @return string
     */
    private static function getViewTableContent(TableUtil $table){
        $contents = [];
        foreach ($table->fields as $field){
            $contents[] = "<el-table-column prop='{$field->name}' :label='labels[\"{$field->name}\"]' sortable='custom' show-overflow-tooltip></el-table-column>";
        }
        CodeUtil::Format($contents, 4);
        return implode("\n", $contents);
    }

    /**
     * 获取模板表单内容
     *
     * @param TableUtil $table
     * @param string $varName
     * @param int $level
     * @return string
     */
    private static function getViewFormContent(TableUtil $table, string $varName, int $level){
        $contents = [];
        foreach ($table->fields as $field){
            if(in_array($field->name, ["id", "created_at", "updated_at"]))
                continue;
            $contents[] = "<el-form-item :label='labels[\"{$field->name}\"]' :error='{$varName}.errors.{$field->name}'><el-input v-model='{$varName}.model.{$field->name}'></el-input></el-form-item>";
        }
        CodeUtil::Format($contents, $level);
        return implode("\n", $contents);
    }

    /**
     * 获取模板详情内容
     *
     * @param TableUtil $table
     * @param string $varName
     * @param int $level
     * @return string
     */
    private static function getViewDetailContent(TableUtil $table, string $varName, int $level){
        $contents = [];
        foreach ($table->fields as $field){
            $contents[] = "<el-descriptions-item :label='labels[\"{$field->name}\"]'><span v-text='{$varName}.model.{$field->name}'/></el-descriptions-item>";
        }
        CodeUtil::Format($contents, $level);
        return implode("\n", $contents);
    }

    /**
     * 获取模板搜索内容
     *
     * @param TableUtil $table
     * @param string $varName
     * @param int $level
     * @return string
     */
    private static function getViewSearchContent(TableUtil $table, string $varName, int $level){
        $contents = [];
        foreach ($table->fields as $field){
            if(in_array($field->name, ["created_at", "updated_at"]))
                continue;
            $contents[] = "<el-form-item :label='labels[\"{$field->name}\"]'><el-input v-model='{$varName}.model.{$field->name}'></el-input></el-form-item>";
        }
        CodeUtil::Format($contents, $level);
        return implode("\n", $contents);
    }
}
