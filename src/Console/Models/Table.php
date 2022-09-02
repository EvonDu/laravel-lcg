<?php

namespace Lcg\Console\Models;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * TableModel
 *
 * 关于单复数约定说明：
 *  模型名：单数
 *  控制器名：单数
 *  路由名：复数
 *  数据库表：复数
 */
class Table{
    /**
     * 常量属性
     */
    static public $NAME_CAMEL_SINGULAR = 0; //驼峰法单数
    static public $NAME_CAMEL_PLURAL = 1;   //驼峰法复数
    static public $NAME_UNDER_SINGULAR = 2; //下划线单数
    static public $NAME_UNDER_PLURAL = 3;   //下划线复数

    /**
     * 成员属性
     */
    public $table;
    public $model;
    public $url;
    public $primary_key = "";
    public $fields = [];

    /**
     * 构造函数
     *
     * @param $table
     * @throws Exception
     */
    public function __construct($table){
        //保存表名
        $this->table = $table;

        //解析名称
        $name = $this->parseName($table);
        $this->url = $name[self::$NAME_UNDER_PLURAL];
        $this->model = $name[self::$NAME_CAMEL_SINGULAR];

        //判断存在
        $exist = DB::select("show tables like '$table'");
        if(empty($exist)){
            throw new Exception("表[$table]不存在与数据库中");
        }

        //获取字段
        $fieldsData = DB::select("desc $table");
        foreach ($fieldsData as $fieldData){
            //构建字段模型
            $fieldModel = new TableField($fieldData);
            //判断是否为主键
            if($fieldModel->isPk)
                $this->primary_key = $fieldModel;
            //添加到字段列表
            $this->fields[] = $fieldModel;
        }
    }

    /**
     * 获取制表字符
     *
     * @param $level
     * @return string
     */
    private function getTabString($level=2){
        $result = "";
        for ($i=0; $i<$level; $i++){
            $result .= "    ";
        }
        return $result;
    }

    /**
     * 解析模型名称
     *
     * @param $name
     * @return string[]
     */
    private function parseName($name){
        //定义返回
        $result = ["", "", "", ""];

        //分割名字构成元素
        $names = explode("_", $name);

        //预处理
        $names[0] = (substr($names[0], -1) === "s") ? substr($names[0], 0, strlen($names[0])-1) : $names[0]; //删除首段复数
        foreach ($names as $index => $name){ //全部改为小写
            $names[$index] = strtolower(trim($name));
        }

        //开始组装
        foreach ($names as $index => $name){
            if($index === 0){
                $result[self::$NAME_CAMEL_SINGULAR] .= ucfirst($name);
                $result[self::$NAME_CAMEL_PLURAL] .= ucfirst($name) . "s";
                $result[self::$NAME_UNDER_SINGULAR] .= $name;
                $result[self::$NAME_UNDER_PLURAL] .= $name . "s";
            } else {
                $result[self::$NAME_CAMEL_SINGULAR] .= ucfirst($name);
                $result[self::$NAME_CAMEL_PLURAL] .= ucfirst($name);
                $result[self::$NAME_UNDER_SINGULAR] .= "_" . $name;
                $result[self::$NAME_UNDER_PLURAL] .= "_" . $name;
            }
        }

        //返回结果
        return $result;
    }

    /**
     * 获取模型注解内容
     * @return string
     */
    public function getModelAnnotateContent(){
        $contents = [];
        $contents[] = "/**";
        $contents[] = "/* {$this->model}";
        foreach ($this->fields as $field){
            $contents[] = " * @property {$field->type} \${$field->name}";
        }
        $contents[] = " */";
        return implode("\n", $contents);
    }

    /**
     * 获取模型字段映射
     * @return string
     */
    public function getModelFieldsContent(){
        $contents = [];
        foreach ($this->fields as $field){
            $contents[] = $this->getTabString(3) . "'{$field->name}' => '{$field->type}',";
        }
        if(isset($contents[0]))
            $contents[0] = str_replace($this->getTabString(3), "", $contents[0]);
        return implode("\n", $contents);
    }

    /**
     * 获取模型标签映射
     *
     * @return string
     */
    public function getModelLabelsContent(){
        $contents = [];
        foreach ($this->fields as $field){
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
    public function getModelRulesContent(){
        $contents = [];
        foreach ($this->fields as $field){
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
    public function getBladeSearchContent(){
        $contents = [];
         foreach ($this->fields as $field){
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
    public function getBladeTableContent(){
        $contents = [];
        foreach ($this->fields as $field){
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
    public function getBladeFormContent(){
        $contents = [];
        foreach ($this->fields as $field){
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
    public function getBladeViewContent(){
        $contents = [];
        foreach ($this->fields as $field){
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
    public function getSwaggerFieldsContent(){
        $contents = [];
        $examples = [];
        foreach ($this->fields as $field){
            if(in_array($field->name, ["created_at", "updated_at", $this->primary_key->name]))
                continue;
            $contents[] = "     *              @OA\Property(description=\"{$field->name}\", property=\"{$field->name}\", type=\"{$field->type}\"),";
            $examples[$field->name] = "";
        }
        $contents[] = "     *              example=" . json_encode($examples);
        $contents[0] = str_replace("     *              ", "", $contents[0]);
        return implode("\n", $contents);
    }
}
