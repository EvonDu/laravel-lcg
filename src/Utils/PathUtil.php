<?php

namespace Lcg\Utils;

class PathUtil
{
    /**
     * 获取两地址的相对路径
     * @param $base String 基础路径
     * @param $target String 目标路径
     * @return string 相对路径
     */
    public static function getRelativePath(string $base, string $target): string{
        $base = str_ireplace("\\", "/", $base);
        $target = str_ireplace("\\", "/", $target);
        return str_ireplace($base, ".", $target);
    }
}
