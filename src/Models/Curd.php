<?php

namespace Lcg\Models;

use Illuminate\Support\Str;
use Lcg\Utils\PathUtil;

class Curd{
    /**
     * @var String $table
     */
    public $table;

    /**
     * @var string $name
     */
    public $name;

    /**
     * @var String[] $prefixes
     */
    public $prefixes;

    /**
     * 构造函数
     */
    public function __construct($table, $options=[])
    {
        //表名相关
        $this->table = $table;
        //名称设置
        $this->name = $options["name"] ?: $table;
        //路径前缀
        $this->prefixes = PathUtil::explodePath($options["prefixes"] ?: "");
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
        $result = Str::studly($this->name);
        return Str::singular($result);
    }

    /**
     * 获取模型名空间
     * @return string
     */
    public function getModelNamespace(){
        $element = ["App", "Models"];
        foreach ($this->prefixes as $prefix){
            $element[] = Str::studly($prefix);
        }
        $element = array_filter($element);
        return implode("\\", $element);
    }

    /**
     * 获取模型类全名
     * @return string
     */
    public function getModelClassname(){
        return PathUtil::tidyPath($this->getModelNamespace() . "\\" . $this->getModelName(), "\\");
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
        foreach ($this->prefixes as $prefix){
            $element[] = Str::studly($prefix);
        }
        $element = array_filter($element);
        return implode("\\", $element);
    }

    /**
     * 获取控制器类全名
     * @return string
     */
    public function getControllerClassname(){
        return PathUtil::tidyPath($this->getControllerNamespace() . "\\" . $this->getControllerName(), "\\");
    }

    /**
     * 获取路径
     * @return string
     */
    public function getPath($separator = "/"){
        $element = [];
        foreach ($this->prefixes as $prefix){
            $element[] = Str::studly($prefix);
        }
        $element = array_filter($element);
        return implode($separator, $element);
    }

    /**
     * 获取视图路径
     * @return string
     */
    public function getViewPath(){
        $element = [
            $this->getPath(),
            $this->getModelName(),
        ];
        $element = array_filter($element);
        return implode("/", $element);
    }

    /**
     * 获取视图URL
     * @return string
     */
    public function getUrl(){
        $element = [];
        foreach ($this->prefixes as $prefix){
            $element[] = Str::snake($prefix);
        }
        $element[] = Str::plural(Str::snake($this->name));
        $element = array_filter($element);
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
