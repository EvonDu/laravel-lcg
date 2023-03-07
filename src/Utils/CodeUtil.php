<?php

namespace Lcg\Utils;

class CodeUtil{
    /**
     * @param $rows array 代码行
     * @param $level integer 跳格数
     * @return void
     */
    public static function Format(&$rows, $level = 2){
        foreach ($rows as $num=>$content){
            if($num == 0)
                continue;
            $rows[$num] = str_repeat("    ",$level) . $content;
        }
    }
}
