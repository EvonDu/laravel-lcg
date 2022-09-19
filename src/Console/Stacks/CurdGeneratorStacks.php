<?php

namespace Lcg\Console\Stacks;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Lcg\Utils\CurdUtil;
use Lcg\Utils\TableUtil;

trait CurdGeneratorStacks
{
    /**
     * 生成视图文件
     *
     * @param TableUtil $table
     * @param CurdUtil $mvc
     * @param bool $isCover
     * @return void
     */
    protected function curdGeneratorViewStack(TableUtil $table, CurdUtil $mvc, bool $isCover=false){
        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/View.vue");
        $content = str_replace("__MODEL_PK__", $table->primary_key->name, $content);
        $content = str_replace("__MODEL_NAME__", $mvc->getModelName(), $content);
        $content = str_replace("__FORM_ITEMS__", $this->getViewFormContent($table), $content);
        $content = str_replace("__TABLE_ITEMS__", $this->getViewTableContent($table), $content);
        $content = str_replace("__DETAIL_ITEMS__", $this->getViewDetailContent($table), $content);
        $content = str_replace("__SEARCH_ITEMS__", $this->getViewSearchContent($table), $content);

        //生成文件
        $filename = base_path("resources/js/Pages/{$mvc->getPath()}/{$mvc->getModelName()}.vue");
        if(!is_file($filename) || $isCover){
            //保存文件
            (new Filesystem)->ensureDirectoryExists(dirname($filename));
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        }
        else {
            //显示记录
            $this->warn("[WARRING] Exist: $filename");
        }
    }

    /**
     * 生成模型文件
     *
     * @param TableUtil $table
     * @param CurdUtil $mvc
     * @param bool $isCover
     * @return void
     */
    protected function curdGeneratorModelStack(TableUtil $table, CurdUtil $mvc, bool $isCover=false){
        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/Model.php");
        $content = str_replace("__MODEL_NAME__", $mvc->getModelName(), $content);
        $content = str_replace("__MODEL_TABLE__", $mvc->getTableName(), $content);
        $content = str_replace("__MODEL_NAMESPACE__", $mvc->getModelNamespace(), $content);
        $content = str_replace("/** MODEL_ANNOTATE */", $this->getModelAnnotateContent($table, $mvc), $content);
        $content = str_replace("/** MODEL_FIELDS */", $this->getModelFieldsContent($table), $content);
        $content = str_replace("/** MODEL_LABELS */", $this->getModelLabelsContent($table), $content);
        $content = str_replace("/** MODEL_RULES */", $this->getModelRulesContent($table), $content);
        $content = str_replace("/** MODEL_FK_RELEVANCE */", $this->getModelRelevanceContent($table), $content);

        //生成文件
        $filename = base_path("app/Models/{$mvc->getPath()}/{$mvc->getModelName()}.php");
        if(!is_file($filename) || $isCover){
            //保存文件
            (new Filesystem)->ensureDirectoryExists(dirname($filename));
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        }
        else {
            //显示记录
            $this->warn("[WARRING] Exist: $filename");
        }
    }

    /**
     * 生成控制器文件
     *
     * @param TableUtil $table
     * @param CurdUtil $mvc
     * @param bool $isCover
     * @return void
     */
    protected function curdGeneratorControllerStack(TableUtil $table, CurdUtil $mvc, bool $isCover=false){
        //引入列表
        $use_list = [ "use {$mvc->getModelClassname()};" ];
        if($mvc->getPath()){
            $use_list[] = "use App\\Http\\Controllers\\Controller;";
        }
        $controller_uses = implode("\n", $use_list);

        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/Controller.php");
        $content = str_replace("__CONTROLLER_NAME__", $mvc->getControllerName(), $content);
        $content = str_replace("__CONTROLLER_NAMESPACE__", $mvc->getControllerNamespace(), $content);
        $content = str_replace("/** CONTROLLER_USES */", $controller_uses, $content);
        $content = str_replace("__MODEL_NAME__", $mvc->getModelName(), $content);
        $content = str_replace("__MODEL_PK__", $table->primary_key->name, $content);
        $content = str_replace("__MODEL_PK_TYPE__", $table->primary_key->type, $content);
        $content = str_replace("__MODEL_SWAGGER_FIELDS__", $this->getSwaggerFieldsContent($table), $content);
        $content = str_replace("__BASE_URL__", $mvc->getUrl(), $content);
        $content = str_replace("__VIEW_PATH__", $mvc->getViewPath(), $content);

        //生成文件
        $filename = base_path("app/Http/Controllers/{$mvc->getPath()}/{$mvc->getControllerName()}.php");
        if(!is_file($filename) || $isCover){
            //保存文件
            (new Filesystem)->ensureDirectoryExists(dirname($filename));
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        }
        else {
            //显示记录
            $this->warn("[WARRING] Exist: $filename");
        }
    }

    /**
     * 获取制表字符
     *
     * @param $level
     * @return string
     */
    private function getTabString($level = 2){
        $result = "";
        for ($i=0; $i<$level; $i++){
            $result .= "    ";
        }
        return $result;
    }

    /**
     * 获取模型注解内容
     *
     * @param TableUtil $table
     * @param CurdUtil $name
     * @param string $prefix
     * @return string
     */
    private function getModelAnnotateContent(TableUtil $table, CurdUtil $curd){
        //注解开始
        $contents = [];
        $contents[] = "/**";
        //属性注解
        $contents[] = "/* " . $curd->getModelName();
        foreach ($table->fields as $field){
            $contents[] = " * @property {$field->type} \${$field->name} {$field->comment}";
        }
        foreach ($table->foreign_keys as $foreign_key){
            if($foreign_key["type"] === "one"){
                $fk_model = Str::studly(Str::singular($foreign_key["referenced_table"]));
                $contents[] = " * @property {$fk_model} \$" . Str::singular($fk_model);
            } else {
                $fk_model = Str::studly(Str::singular($foreign_key["table"]));
                $contents[] = " * @property {$fk_model}[] \$" . Str::plural($fk_model);
            }
        }
        //文档注解
        $contents[] = "";
        $contents[] = " * @OA\Schema(schema=\"{$curd->getModelName()}\", description=\"\")";
        foreach ($table->fields as $field){
            $contents[] = " * @OA\Property(property=\"{$field->name}\", type=\"{$field->type}\", description=\"{$field->comment}\")";
        }
        foreach ($table->foreign_keys as $foreign_key){
            if($foreign_key["type"] === "one"){
                $fk_model = Str::studly(Str::singular($foreign_key["referenced_table"]));
                $contents[] = " * @OA\Property(property=\"$fk_model\", ref=\"#/components/schemas/$fk_model\")";
            } else {
                $fk_model = Str::studly(Str::singular($foreign_key["table"]));
                $contents[] = " * @OA\Property(property=\"$fk_model\", type=\"array\", @OA\Items(ref=\"#/components/schemas/$fk_model\"))";
            }
        }
        //注解结尾
        $contents[] = " */";
        return implode("\n", $contents);
    }

    /**
     * 获取模型字段映射
     *
     * @param TableUtil $table
     * @param string $prefix
     * @return string
     */
    private function getModelFieldsContent(TableUtil $table){
        $contents = [];
        foreach ($table->fields as $field){
            $contents[] = $this->getTabString(3) . "'{$field->name}' => '{$field->type}',";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(3), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模型外键映射
     *
     * @param TableUtil $table
     * @param string $prefix
     * @return string
     */
    private function getModelRelevanceContent(TableUtil $table){
        $contents = [];
        foreach ($table->foreign_keys as $foreign_key){
            //添加分割空行
            if(count($contents) > 0)
                $contents[] = "";
            //根据类型生成
            if($foreign_key["type"] === "one"){
                $fk_mvc = new CurdUtil($foreign_key["referenced_table"]);
                $fk_var = Str::singular($fk_mvc->getModelName());
                $contents[] = $this->getTabString(1) . "/**";
                $contents[] = $this->getTabString(1) . " * Property - {$fk_mvc->getModelName()}";
                $contents[] = $this->getTabString(1) . " * @return \Illuminate\Database\Eloquent\Relations\BelongsTo";
                $contents[] = $this->getTabString(1) . " */";
                $contents[] = $this->getTabString(1) . "public function {$fk_var}(){";
                $contents[] = $this->getTabString(2) . "return \$this->belongsTo({$fk_mvc->getModelName()}::class, '{$foreign_key["column"]}', '{$foreign_key["referenced_column"]}');";
                $contents[] = $this->getTabString(1) . "}";
            }else{
                $fk_mvc = new CurdUtil($foreign_key["table"]);
                $fk_var = Str::plural($fk_mvc->getModelName());
                $contents[] = $this->getTabString(1) . "/**";
                $contents[] = $this->getTabString(1) . " * Property - {$fk_mvc->getModelName()}";
                $contents[] = $this->getTabString(1) . " * @return \Illuminate\Database\Eloquent\Relations\HasMany";
                $contents[] = $this->getTabString(1) . " */";
                $contents[] = $this->getTabString(1) . "public function {$fk_var}(){";
                $contents[] = $this->getTabString(2) . "return \$this->hasMany({$fk_mvc->getModelName()}::class, '{$foreign_key["referenced_column"]}');";
                $contents[] = $this->getTabString(1) . "}";
            }
        }
        if(isset($contents[0]))
            $contents[0] = ltrim($contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模型标签映射
     *
     * @param TableUtil $table
     * @param string $prefix
     * @return string
     */
    private function getModelLabelsContent(TableUtil $table){
        $contents = [];
        foreach ($table->fields as $field){
            $contents[] = $this->getTabString(3) . "'{$field->name}' => '{$field->label}',";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(3), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模型验证规则
     *
     * @param TableUtil $table
     * @param string $prefix
     * @return string
     */
    private function getModelRulesContent(TableUtil $table){
        $contents = [];
        foreach ($table->fields as $field){
            if(in_array($field->name, ["id", "created_at", "updated_at"]))
                continue;
            $contents[] = $this->getTabString(3) . "'{$field->name}' => '{$field->rules}',";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(3), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模板表格内容
     *
     * @param TableUtil $table
     * @return string
     */
    private function getViewTableContent(TableUtil $table){
        $contents = [];
        foreach ($table->fields as $field){
            $contents[] = $this->getTabString(5) . "<el-table-column prop='{$field->name}' :label='labels.{$field->name}' sortable='custom' show-overflow-tooltip></el-table-column>";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(5), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模板表单内容
     *
     * @param TableUtil $table
     * @return string
     */
    private function getViewFormContent(TableUtil $table){
        $contents = [];
        foreach ($table->fields as $field){
            if(in_array($field->name, ["id", "created_at", "updated_at"]))
                continue;
            $contents[] = $this->getTabString(4) . "<el-form-item :label='labels.{$field->name}' :error='form.errors.{$field->name}'>";
            $contents[] = $this->getTabString(5) . "<el-input v-model='form.model.{$field->name}'></el-input>";
            $contents[] = $this->getTabString(4) . "</el-form-item>";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(4), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模板详情内容
     *
     * @param TableUtil $table
     * @return string
     */
    private function getViewDetailContent(TableUtil $table){
        $contents = [];
        foreach ($table->fields as $field){
            //$contents[] = $this->getTabString(4) . "<el-descriptions-item :label='labels.{$field->name}'>@{{ view.model.{$field->name} }}</el-descriptions-item>";
            $contents[] = $this->getTabString(4) . "<el-descriptions-item :label='labels.{$field->name}'><span v-text='detail.model.{$field->name}'/></el-descriptions-item>";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(4), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模板搜索内容
     *
     * @param TableUtil $table
     * @return string
     */
    private function getViewSearchContent(TableUtil $table){
        $contents = [];
        foreach ($table->fields as $field){
            if(in_array($field->name, ["created_at", "updated_at"]))
                continue;
            $contents[] = $this->getTabString(4) . "<el-form-item :label='labels.{$field->name}'>";
            $contents[] = $this->getTabString(5) . "<el-input v-model='search.temp.{$field->name}'></el-input>";
            $contents[] = $this->getTabString(4) . "</el-form-item>";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(4), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取Swagger字段描述
     *
     * @param TableUtil $table
     * @return string
     */
    private function getSwaggerFieldsContent(TableUtil $table){
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
