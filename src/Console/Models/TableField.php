<?php

namespace Lcg\Console\Models;

class TableField{
    public $name;
    public $type;
    public $length;
    public $default;
    public $comment;
    public $dbType;
    public $isPk = false;
    public $allowNull = false;
    public $label = "";
    public $rules = "";

    /**
     * 构造函数
     * @param $fieldData
     */
    public function __construct($fieldData){
        $this->name = $fieldData->Field;
        $this->type = $this->getType($fieldData->Type);
        $this->length = $this->getLength($fieldData->Type);
        $this->default = $fieldData->Default;
        $this->dbType = $fieldData->Type;
        $this->comment = $fieldData->Comment;

        if($fieldData->Key === "PRI")
            $this->isPk = true;
        if($fieldData->Null === "YES")
            $this->allowNull = true;

        $this->label = $this->getLabel();
        $this->rules = $this->getRules();
    }

    /**
     * 获取数据类型
     *
     * @param $databaseType
     * @return array|string|string[]|null
     */
    public function getType($databaseType){
        $type = preg_replace('/\(\d*\)/', "", $databaseType);
        switch ($type){
            case "char":
            case "varchar":
            case "datetime":
                return "string";
            case "int":
                return "integer";
            default:
                return $type;
        }
    }

    /**
     * 获取数据长度
     *
     * @param $databaseType
     * @return int|mixed
     */
    public function getLength($databaseType){
        preg_match_all('/\((.*?)\)/', $databaseType, $match);
        if(isset($match[1][0]))
            return $match[1][0];
        else
            return null;
    }

    /**
     * 获取标签名称
     *
     * @return string
     */
    public function getLabel(){
        if(!empty($this->comment))
            return $this->comment;
        else
            return ucwords(str_replace("_", " ", $this->name));
    }

    /**
     * 获取验证规则
     *
     * @return string
     */
    public function getRules(){
        //构建列表
        $rules = [];

        //添加规则
        $rules[] = $this->type;
        if(!$this->allowNull)
            $rules[] = "required";
        if($this->type === "string" && $this->length !== null)
            $rules[] = "between:0,{$this->length}";

        //返回结果
        return implode("|", $rules);
    }
}
