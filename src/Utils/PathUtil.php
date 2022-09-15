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
     * 展开相关路径
     *
     * @param string $path 基础路径
     * @return string[] 路径元素
     */
    public static function explodePath(string $path){
        $path = self::tidyPath($path, "/");
        return explode("/", $path);
    }
}
