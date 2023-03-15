<?php

namespace Lcg\Console\Traits;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

trait Codes
{
    /**
     * 输出代码
     *
     * @param Command $command
     * @param string $filename
     * @param string $content
     * @param bool $cover
     * @return void
     */
    private static function put(Command $command, string $filename, string $content, bool $cover){
        if(!is_file($filename) || $cover){
            //保存文件
            (new Filesystem)->ensureDirectoryExists(dirname($filename));
            file_put_contents($filename, $content);
            //显示记录
            $command->info("[APPEND] $filename");
        }
        else {
            //显示记录
            $command->warn("[WARRING] Exist: $filename");
        }
    }
}
