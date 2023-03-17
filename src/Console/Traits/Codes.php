<?php

namespace Lcg\Console\Traits;

use Illuminate\Console\View\Components\Factory;
use Illuminate\Filesystem\Filesystem;

trait Codes
{
    /**
     * 输出代码
     *
     * @param Factory $factory
     * @param string $filename
     * @param string $content
     * @param bool $cover
     * @return void
     */
    private static function put(Factory $factory, string $filename, string $content, bool $cover){
        $filename = preg_replace("/[\/|\\\]+/i", DIRECTORY_SEPARATOR, $filename);
        if(!is_file($filename) || $cover){
            //保存文件
            (new Filesystem)->ensureDirectoryExists(dirname($filename));
            file_put_contents($filename, $content);
            //显示记录
            $factory->twoColumnDetail("<fg=#2E8B57>GENERATOR</> $filename", "<fg=green;options=bold>DONE</>");
        }
        else {
            //显示记录
            $factory->twoColumnDetail("<fg=#2E8B57>GENERATOR</> $filename", "<fg=yellow;options=bold>EXIST</>");
        }
    }
}
