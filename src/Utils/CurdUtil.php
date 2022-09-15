<?php

namespace Lcg\Utils;

use Illuminate\Support\Str;

class CurdUtil{
    /**
     * @var String $table
     */
    public $table;

    /**
     * @var String[] $prefixs
     */
    public $prefixs;

    /**
     * 构造函数
     */
    public function __construct($table, $path = "")
    {
        //表名相关
        $this->table = $table;
        //路径前缀
        $this->prefixs = PathUtil::explodePath($path ?: "");
    }

    /**
     * 获取表名
     * @return String
     */
    public function getTableName(){
        return $this->table;
    }

    /**
     * 获取模型名
     * @return string
     */
    public function getModelName(){
        $result = Str::studly($this->table);
        return Str::singular($result);
    }

    /**
     * 获取模型名空间
     * @return string
     */
    public function getModelNamespace(){
        $element = ["App", "Models"];
        foreach ($this->prefixs as $prefix){
            $element[] = Str::studly($prefix);
        }
        return implode("\\", $element);
    }

    /**
     * 获取模型类全名
     * @return string
     */
    public function getModelClassname(){
        return $this->getModelNamespace() . "\\" . $this->getModelName();
    }

    /**
     * 获取控制器名
     * @return string
     */
    public function getControllerName(){
        return $this->getModelName() . "Controller";
    }

    /**
     * 获取控制器名空间
     * @return string
     */
    public function getControllerNamespace(){
        $element = ["App", "Http", "Controllers"];
        foreach ($this->prefixs as $prefix){
            $element[] = Str::studly($prefix);
        }
        return implode("\\", $element);
    }

    /**
     * 获取控制器类全名
     * @return string
     */
    public function getControllerClassname(){
        return $this->getControllerNamespace() . "\\" . $this->getControllerName();
    }

    /**
     * 获取路径
     * @return string
     */
    public function getPath($separator = "/"){
        $element = [];
        foreach ($this->prefixs as $prefix){
            $element[] = Str::studly($prefix);
        }
        return implode($separator, $element);
    }

    /**
     * 获取视图URL
     * @return string
     */
    public function getUrl(){
        $element = [];
        foreach ($this->prefixs as $prefix){
            $element[] = Str::snake($prefix);
        }
        $element[] = Str::plural(Str::snake($this->table));
        return implode("/", $element);
    }

    /**
     * 获取接口URL
     * @return string
     */
    public function getApiUrl(){
        return "api/" . $this->getUrl();
    }
}
