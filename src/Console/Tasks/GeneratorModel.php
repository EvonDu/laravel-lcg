<?php
namespace Lcg\Console\Tasks;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lcg\Console\Traits\Codes;
use Lcg\Models\Curd;
use Lcg\Models\Table;
use Lcg\Utils\CodeUtil;

class GeneratorModel{
    /**
     * 引入特征
     */
    Use Codes;

    /**
     * 执行生成
     *
     * @param Command $command
     * @param Table $table
     * @param Curd $curd
     * @param bool $cover
     * @return void
     */
    public static function run(Command $command, Table $table, Curd $curd, bool $cover=false){
        //读取模板
        $content = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/stubs/curd/common/Model.php");
        $content = str_replace("__MODEL_NAME__", $curd->getModelName(), $content);
        $content = str_replace("__MODEL_TABLE__", $curd->getTableName(), $content);
        $content = str_replace("__MODEL_NAMESPACE__", $curd->getModelNamespace(), $content);
        $content = str_replace("/** MODEL_SETTINGS */", self::getModelSettingsContent($table), $content);
        $content = str_replace("/** MODEL_ANNOTATE */", self::getModelAnnotateContent($table, $curd), $content);
        $content = str_replace("/** MODEL_FIELDS */", self::getModelFieldsContent($table), $content);
        $content = str_replace("/** MODEL_LABELS */", self::getModelLabelsContent($table), $content);
        $content = str_replace("/** MODEL_RULES */", self::getModelRulesContent($table), $content);
        $content = str_replace("/** MODEL_FK_RELEVANCE */", self::getModelRelevanceContent($table), $content);

        //清理空白
        $content = self::clearEmptyContent($content);

        //生成文件
        self::put($command, base_path("app/Models/{$curd->getPath()}/{$curd->getModelName()}.php"), $content, $cover);
    }

    /**
     * 清理模型空白内容
     *
     * @param string $content
     * @return array|string|string[]|null
     */
    private static function clearEmptyContent(string $content){
        return preg_replace('/\n\n +\n/', "\n", $content);
    }

    /**
     * 获取模型注解内容
     *
     * @param Table $table
     * @param Curd $curd
     * @return string
     */
    private static function getModelAnnotateContent(Table $table, Curd $curd){
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
     * @param Table $table
     * @return string
     */
    private static function getModelFieldsContent(Table $table){
        $contents = [];
        foreach ($table->fields as $field){
            $contents[] = "'{$field->name}' => '{$field->type}',";
        }
        CodeUtil::Format($contents, 3);
        return implode("\n", $contents);
    }

    /**
     * 获取模型外键映射
     *
     * @param Table $table
     * @return string
     */
    private static function getModelRelevanceContent(Table $table){
        $contents = [];
        foreach ($table->foreign_keys as $foreign_key){
            //添加分割空行
            if(count($contents) > 0)
                $contents[] = "";
            //根据类型生成
            if($foreign_key["type"] === "one"){
                $fk_mvc = new Curd($foreign_key["referenced_table"]);
                $fk_var = Str::singular($fk_mvc->getModelName());
                $contents[] = "/**";
                $contents[] = " * Property - {$fk_mvc->getModelName()}";
                $contents[] = " * @return \Illuminate\Database\Eloquent\Relations\BelongsTo";
                $contents[] = " */";
                $contents[] = "public function {$fk_var}(){";
                $contents[] = "    return \$this->belongsTo({$fk_mvc->getModelName()}::class, '{$foreign_key["column"]}', '{$foreign_key["referenced_column"]}');";
                $contents[] = "}";
            }else{
                $fk_mvc = new Curd($foreign_key["table"]);
                $fk_var = Str::plural($fk_mvc->getModelName());
                $contents[] = "/**";
                $contents[] = " * Property - {$fk_mvc->getModelName()}";
                $contents[] = " * @return \Illuminate\Database\Eloquent\Relations\HasMany";
                $contents[] = " */";
                $contents[] = "public function {$fk_var}(){";
                $contents[] = "    return \$this->hasMany({$fk_mvc->getModelName()}::class, '{$foreign_key["referenced_column"]}');";
                $contents[] = "}";
            }
        }
        CodeUtil::Format($contents, 1);
        return implode("\n", $contents);
    }

    /**
     * 获取模型标签映射
     *
     * @param Table $table
     * @return string
     */
    private static function getModelLabelsContent(Table $table){
        $contents = [];
        foreach ($table->fields as $field){
            $contents[] = "'{$field->name}' => '{$field->label}',";
        }
        CodeUtil::Format($contents, 3);
        return implode("\n", $contents);
    }

    /**
     * 获取模型验证规则
     *
     * @param Table $table
     * @return string
     */
    private static function getModelRulesContent(Table $table){
        $contents = [];
        foreach ($table->fields as $field){
            if(in_array($field->name, ["id", "created_at", "updated_at"]))
                continue;
            $contents[] = "'{$field->name}' => '{$field->rules}',";
        }
        CodeUtil::Format($contents, 3);
        return implode("\n", $contents);
    }

    /**
     * 获取模型相关设置
     *
     * @param Table $table
     * @return string
     */
    private static function getModelSettingsContent(Table $table){
        //代码行
        $codes = [];

        //字段映射
        $codes_casts = self::getModelCastCodes($table);
        if($codes_casts)
            $codes = array_merge($codes, $codes_casts, [""]);

        //时间字段
        $codes_timestamps = self::getModelTimestampsCodes($table);
        if($codes_timestamps)
            $codes = array_merge($codes, $codes_timestamps, [""]);

        //删除末尾
        if(count($codes) > 0)
            array_pop($codes);

        //格式化输出
        CodeUtil::Format($codes, 1);
        return implode("\n", $codes);
    }

    /**
     * 获取模型相关设置-类型映射
     *
     * @param Table $table
     * @return array
     */
    private static function getModelCastCodes(Table $table){
        $casts = [];
        foreach ($table->fields as $field){
            switch ($field->dbType){
                case "json":
                    $casts[] = "'{$field->name}' => 'array'";
                    break;
            }
        }

        $codes = [];
        if($casts){
            $codes[] = '/**';
            $codes[] = ' * Casts';
            $codes[] = ' *';
            $codes[] = ' * @var array';
            $codes[] = ' */';
            $codes[] = 'protected $casts = [' . implode(", ", $casts) . '];';
        }

        return $codes;
    }

    /**
     * 获取模型相关设置-时间字段
     *
     * @param Table $table
     * @return array
     */
    private static function getModelTimestampsCodes(Table $table){
        $fields = [];
        foreach ($table->fields as $field){
            $fields[] = $field->name;
        }

        $codes = [];
        if(!(in_array("created_at", $fields) && in_array("updated_at", $fields))){
            $codes[] = '/**';
            $codes[] = ' * Timestamps';
            $codes[] = ' *';
            $codes[] = ' * @var bool';
            $codes[] = ' */';
            $codes[] = 'public $timestamps = true;';
        }

        return $codes;
    }
}
