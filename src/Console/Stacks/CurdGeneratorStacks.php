<?php

namespace Lcg\Console\Stacks;

use Lcg\Utils\NameUtil;
use Lcg\Utils\TableUtil;

trait CurdGeneratorStacks
{
    /**
     * 生成视图文件
     *
     * @param TableUtil $model
     * @param NameUtil $name
     * @param bool $isCover
     * @return void
     */
    protected function curdGeneratorViewStack(TableUtil $model, NameUtil $name, bool $isCover=false){
        //获取内容
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/View.vue");

        //替换内容
        $content = str_replace("__MODEL_PK__", $model->primary_key->name, $content);
        $content = str_replace("__MODEL_NAME__", $name->getPascal(), $content);
        $content = str_replace("__FORM_ITEMS__", $this->getBladeFormContent($model, $name), $content);
        $content = str_replace("__VIEW_ITEMS__", $this->getBladeViewContent($model, $name), $content);
        $content = str_replace("__TABLE_ITEMS__", $this->getBladeTableContent($model, $name), $content);
        $content = str_replace("__SEARCH_ITEMS__", $this->getBladeSearchContent($model, $name), $content);

        //生成文件
        $filename = base_path("resources/js/Pages/{$name->getPascal()}.vue");
        if(!is_file($filename) || $isCover){
            //保存文件
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        } else {
            //显示记录
            $this->warn("[WARRING] Exist: $filename");
        }
    }

    /**
     * 生成模型文件
     *
     * @param TableUtil $model
     * @param NameUtil $name
     * @param bool $isCover
     * @return void
     */
    protected function curdGeneratorModelStack(TableUtil $model, NameUtil $name, bool $isCover=false){
        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/Model.php");
        $content = str_replace("__MODEL_NAME__", $name->getPascal(), $content);
        $content = str_replace("__MODEL_TABLE__", $model->table, $content);
        $content = str_replace("/** MODEL_ANNOTATE */", $this->getModelAnnotateContent($model, $name), $content);
        $content = str_replace("/** MODEL_FIELDS */", $this->getModelFieldsContent($model, $name), $content);
        $content = str_replace("/** MODEL_LABELS */", $this->getModelLabelsContent($model, $name), $content);
        $content = str_replace("/** MODEL_RULES */", $this->getModelRulesContent($model, $name), $content);
        $content = str_replace("/** MODEL_FK_RELEVANCE */", $this->getModelRelevanceContent($model, $name), $content);

        //生成文件
        $filename = base_path("app/Models/{$name->getPascal()}.php");
        if(!is_file($filename) || $isCover){
            //保存文件
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        } else {
            //显示记录
            $this->warn("[WARRING] Exist: $filename");
        }
    }

    /**
     * 生成控制器文件
     *
     * @param TableUtil $model
     * @param NameUtil $name
     * @param bool $isCover
     * @return void
     */
    protected function curdGeneratorControllerStack(TableUtil $model, NameUtil $name, bool $isCover=false){
        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/Controller.php");
        $content = str_replace("__MODEL_NAME__", $name->getPascal(), $content);
        $content = str_replace("__MODEL_URL__", $name->getUnder(true), $content);
        $content = str_replace("__MODEL_PK__", $model->primary_key->name, $content);
        $content = str_replace("__MODEL_PK_TYPE__", $model->primary_key->type, $content);
        $content = str_replace("__MODEL_SWAGGER_FIELDS__", $this->getSwaggerFieldsContent($model, $name), $content);

        //生成文件
        $filename = base_path("app/Http/Controllers/{$name->getPascal()}Controller.php");
        if(!is_file($filename) || $isCover){
            //保存文件
            file_put_contents($filename, $content);
            $this->info("[APPEND] $filename");
        } else {
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
     * @return string
     */
    private function getModelAnnotateContent(TableUtil $model, NameUtil $name){
        $contents = [];
        $contents[] = "/**";
        $contents[] = "/* {$name->getPascal()}";
        foreach ($model->fields as $field){
            $contents[] = " * @property {$field->type} \${$field->name} {$field->comment}";
        }
        foreach ($model->foreign_keys as $foreign_key){
            if($foreign_key["type"] === "one"){
                $fk_table = new NameUtil($foreign_key["referenced_table"]);
                $contents[] = " * @property {$fk_table->getPascal()} \${$fk_table->getCamel(false)}";
            } else {
                $fk_table = new NameUtil($foreign_key["table"]);
                $contents[] = " * @property {$fk_table->getPascal()}[] \${$fk_table->getCamel(true)}";
            }
        }
        $contents[] = " */";
        return implode("\n", $contents);
    }

    /**
     * 获取模型字段映射
     * @return string
     */
    private function getModelFieldsContent(TableUtil $model, NameUtil $name){
        $contents = [];
        foreach ($model->fields as $field){
            $contents[] = $this->getTabString(3) . "'{$field->name}' => '{$field->type}',";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(3), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模型外键映射
     * @param TableUtil $model
     * @param NameUtil $name
     * @return string
     */
    private function getModelRelevanceContent(TableUtil $model, NameUtil $name){
        $contents = [];
        foreach ($model->foreign_keys as $foreign_key){
            //添加分割空行
            if(count($contents) > 0)
                $contents[] = "";
            //根据类型生成
            if($foreign_key["type"] === "one"){
                $fk_table = new NameUtil($foreign_key["referenced_table"]);
                $contents[] = $this->getTabString(1) . "/**";
                $contents[] = $this->getTabString(1) . " * Property - {$fk_table->getCamel(false)}";
                $contents[] = $this->getTabString(1) . " * @return \Illuminate\Database\Eloquent\Relations\BelongsTo";
                $contents[] = $this->getTabString(1) . " */";
                $contents[] = $this->getTabString(1) . "public function {$fk_table->getCamel(false)}(){";
                $contents[] = $this->getTabString(2) . "return \$this->belongsTo('App\Models\\{$fk_table->getPascal()}', '{$foreign_key["column"]}', '{$foreign_key["referenced_column"]}');";
                $contents[] = $this->getTabString(1) . "}";
            }else{
                $fk_table = new NameUtil($foreign_key["table"]);
                $contents[] = $this->getTabString(1) . "/**";
                $contents[] = $this->getTabString(1) . " * Property - {$fk_table->getCamel(false)}";
                $contents[] = $this->getTabString(1) . " * @return \Illuminate\Database\Eloquent\Relations\HasMany";
                $contents[] = $this->getTabString(1) . " */";
                $contents[] = $this->getTabString(1) . "public function {$fk_table->getCamel(true)}(){";
                $contents[] = $this->getTabString(2) . "return \$this->hasMany('App\Models\\{$fk_table->getPascal()}', '{$foreign_key["column"]}', '{$foreign_key["referenced_column"]}');";
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
     * @return string
     */
    private function getModelLabelsContent(TableUtil $model, NameUtil $name){
        $contents = [];
        foreach ($model->fields as $field){
            $contents[] = $this->getTabString(3) . "'{$field->name}' => '{$field->label}',";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(3), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模型验证规则
     *
     * @return string
     */
    private function getModelRulesContent(TableUtil $model, NameUtil $name){
        $contents = [];
        foreach ($model->fields as $field){
            if(in_array($field->name, ["id", "created_at", "updated_at"]))
                continue;
            $contents[] = $this->getTabString(3) . "'{$field->name}' => '{$field->rules}',";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(3), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模板搜索内容
     *
     * @return string
     */
    private function getBladeSearchContent(TableUtil $model, NameUtil $name){
        $contents = [];
        foreach ($model->fields as $field){
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
     * 获取模板表格内容
     *
     * @return string
     */
    private function getBladeTableContent(TableUtil $model, NameUtil $name){
        $contents = [];
        foreach ($model->fields as $field){
            $contents[] = $this->getTabString(5) . "<el-table-column prop='{$field->name}' :label='labels.{$field->name}' sortable='custom' show-overflow-tooltip></el-table-column>";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(5), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模板表单内容
     *
     * @return string
     */
    private function getBladeFormContent(TableUtil $model, NameUtil $name){
        $contents = [];
        foreach ($model->fields as $field){
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
     * @return string
     */
    private function getBladeViewContent(TableUtil $model, NameUtil $name){
        $contents = [];
        foreach ($model->fields as $field){
            //$contents[] = $this->getTabString(4) . "<el-descriptions-item :label='labels.{$field->name}'>@{{ view.model.{$field->name} }}</el-descriptions-item>";
            $contents[] = $this->getTabString(4) . "<el-descriptions-item :label='labels.{$field->name}'><span v-text='view.model.{$field->name}'/></el-descriptions-item>";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(4), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取Swagger字段描述
     *
     * @return string
     */
    private function getSwaggerFieldsContent(TableUtil $model, NameUtil $name){
        $contents = [];
        $examples = [];
        foreach ($model->fields as $field){
            if(in_array($field->name, ["created_at", "updated_at", $model->primary_key->name]))
                continue;
            $contents[] = "     *              @OA\Property(description=\"{$field->name}\", property=\"{$field->name}\", type=\"{$field->type}\"),";
            $examples[$field->name] = "";
        }
        $contents[] = "     *              example=" . json_encode($examples);
        $contents[0] = str_replace("     *              ", "", $contents[0]);
        return implode("\n", $contents);
    }
}
