<?php

namespace Lcg\Utils;

class NameUtil{
    /**
     * 常量属性
     */
    static public $NAME_PASCAL_SINGULAR = "PASCAL_SINGULAR";    //帕斯卡单数
    static public $NAME_PASCAL_PLURAL   = "PASCAL_PLURAL";      //帕斯卡复数
    static public $NAME_UNDER_SINGULAR  = "UNDER_SINGULAR";     //下划线单数
    static public $NAME_UNDER_PLURAL    = "UNDER_PLURAL";       //下划线复数

    /**
     * 成员属性
     * @var array
     */
    private array $names = [];

    /**
     * 构造函数
     */
    public function __construct($name){
        //分割名字构成元素
        $els = explode("_", $name);

        //预处理
        $els[0] = (substr($els[0], -1) === "s") ? substr($els[0], 0, strlen($els[0])-1) : $els[0]; //删除首段复数
        foreach ($els as $index => $name){ //全部改为小写
            $els[$index] = strtolower(trim($name));
        }

        //开始组装
        $this->names[self::$NAME_PASCAL_SINGULAR] = "";
        $this->names[self::$NAME_PASCAL_PLURAL] = "";
        $this->names[self::$NAME_UNDER_SINGULAR] = "";
        $this->names[self::$NAME_UNDER_PLURAL] = "";
        foreach ($els as $index => $el){
            if($index === 0){
                $this->names[self::$NAME_PASCAL_SINGULAR] .= ucfirst($el);
                $this->names[self::$NAME_PASCAL_PLURAL] .= ucfirst($el) . "s";
                $this->names[self::$NAME_UNDER_SINGULAR] .= $el;
                $this->names[self::$NAME_UNDER_PLURAL] .= $el . "s";
            } else {
                $this->names[self::$NAME_PASCAL_SINGULAR] .= ucfirst($el);
                $this->names[self::$NAME_PASCAL_PLURAL] .= ucfirst($el);
                $this->names[self::$NAME_UNDER_SINGULAR] .= "_" . $el;
                $this->names[self::$NAME_UNDER_PLURAL] .= "_" . $el;
            }
        }
    }

    /**
     * 获取帕斯卡命名法名称
     *
     * @param $isPlural bool 是否复数
     * @return string
     */
    public function getPascal($isPlural = false)
    {
        return $isPlural ? $this->names[self::$NAME_PASCAL_PLURAL] : $this->names[self::$NAME_PASCAL_SINGULAR];
    }

    /**
     * 获取下划线命名法名称
     *
     * @param $isPlural bool 是否复数
     * @return string
     */
    public function getUnder($isPlural = false)
    {
        return $isPlural ? $this->names[self::$NAME_UNDER_PLURAL] : $this->names[self::$NAME_UNDER_SINGULAR];
    }
}
