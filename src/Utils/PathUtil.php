<?php

namespace Lcg\Utils;

class PathUtil
{
    /**
     * 获取两地址的相对路径
     *
     * @param string $path 基础路径
     * @param string $target 目标路径
     * @return string 相对路径
     */
    public static function getRelativePath(string $path, string $target): string{
        $path = str_ireplace("\\", "/", $path);
        $target = str_ireplace("\\", "/", $target);
        return str_ireplace($path, ".", $target);
    }

    /**
     * 修剪相关路径
     * 去除前后分隔符
     *
     * @param string $path 基础路径
     * @param string $type 修剪地方(all:全部, prefix:前缀, suffix:后缀)
     * @return string 目标路径
     */
    public static function trimPath(string $path, string $type = "all"){
        //修剪后面
        while (in_array($type, ["all", "suffix"])){
            if(in_array(substr($path, -1), ["/", "\\"])){
                $path = substr($path, 0, -1);
            } else {
                break;
            }
        }
        //修剪前面
        while (in_array($type, ["all", "prefix"])){
            if(in_array(substr($path, 0, 1), ["/", "\\"])){
                $path = substr($path, 1, strlen($path)-1);
            } else {
                break;
            }
        }
        //返回结果
        return $path;
    }

    /**
     * 整理相关路径
     *
     * @param string $path 基础路径
     * @param string $separator 分割符号(/|\)
     * @return string 目标路径
     */
    public static function tidyPath(string $path, string $separator = "/"): string{
        //去除连续符号
        $path = preg_replace("/([\/|\\\]{2,})/", "/", $path);
        //替换分割符号
        if($separator === "/"){
            return str_replace("\\", "/", $path);
        } else {
            return str_replace("/", "\\", $path);
        }
    }

    /**
     * 连接相关路径
     *
     * @param string $path 基础路径
     * @param string $target 添加节点
     * @param string $separator 分割符号(/|\)
     * @return string 目标路径
     */
    public static function linkPath(string $path, string $target, string $separator = "/"): string{
        //整合路径参数
        if($path && $target)
            $path = $path . $separator . $target;
        else if($path)
            $path = $path;
        else if($target)
            $path = $target;
        else
            $path = "";
        //返回整理路径
        return self::tidyPath($path, $separator);
    }
}
