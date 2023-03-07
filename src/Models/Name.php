<?php

namespace Lcg\Models;

class Name{
    /**
     * 常量属性
     */
    static public $CAMEL  = "CAMEL";        //驼峰命名法
    static public $PASCAL = "PASCAL";       //帕斯卡命名法
    static public $UNDERLINE = "UNDERLINE"; //下划线命名法

    /**
     * 成员属性
     * @var array
     */
    private array $names = [];

    /**
     * 构造函数
     */
    public function __construct($name){
        //分割元素
        $els = $this->explodeName($name);

        //全部改为小写
        foreach ($els as $index => $name){
            $els[$index] = strtolower(trim($name));
        }

        //开始组装
        $this->names[self::$CAMEL] = "";
        $this->names[self::$PASCAL] = "";
        $this->names[self::$UNDERLINE] = "";
        foreach ($els as $index => $el){
            if($index === 0){
                $this->names[self::$CAMEL] = $el;
                $this->names[self::$PASCAL] .= ucfirst($el);
                $this->names[self::$UNDERLINE] .= $el;
            }
            else {
                $this->names[self::$CAMEL] .= ucfirst($el);
                $this->names[self::$PASCAL] .= ucfirst($el);
                $this->names[self::$UNDERLINE] .= "_" . $el;
            }
        }
    }

    /**
     * 展开名称元素
     *
     * @param string $name 字符串
     * @return string[]
     */
    private function explodeName($name){
        //按照规则分割
        if(strpos($name, "_") !== false){
            //存在下划线则以下划线分割
            $result = explode("_", $name);
        }
        else {
            //不存在下划线则以大小写分割
            $result = preg_split('/(?=[A-Z])/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
        }

        //去除空行返回
        return array_filter($result);
    }

    /**
     * 获取帕斯卡命名法名称
     *
     * @param $isPlural bool 是否复数
     * @return string
     */
    public function getPascal()
    {
        return $this->names[self::$PASCAL];
    }

    /**
     * 获取驼峰法命名法名称
     *
     * @param $isPlural bool 是否复数
     * @return string
     */
    public function getCamel()
    {
        return $this->names[self::$CAMEL];
    }

    /**
     * 获取下划线命名法名称
     *
     * @param $isPlural bool 是否复数
     * @return string
     */
    public function getUnderline()
    {
        return $this->names[self::$UNDERLINE];
    }
}
