<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Lcg\Console\Models\Table;
use Lcg\Console\Stacks\CurdGeneratorStacks;
use Lcg\Utils\NameUtil;
use Lcg\Utils\PathUtil;
use Lcg\Utils\TableUtil;

class CurdCommand extends Command
{
    use CurdGeneratorStacks;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcg:curd {table : Table name}
                            {--path= : Generate path}
                            {--cover=n : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate curd of table';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        //获取表名
        $table = $this->argument("table");

        //构建模型
        $table_util = null;
        try{
            $table_util = new TableUtil($table);
        }
        catch (\Exception $e){
            $this->error("[ERROR] ".$e->getMessage());
        }

        //名称工具
        $name_util = new NameUtil($table_util->table);

        //路径参数
        $args_path = $this->option('path') ? $this->option('path') : "";
        $path_prefix = PathUtil::trimPath($args_path);

        //创建试图
        $this->curdGeneratorViewStack($table_util, $name_util, $path_prefix, true);

        //创建模型
        $this->curdGeneratorModelStack($table_util, $name_util, $path_prefix, true);

        //创建控制器
        $this->curdGeneratorControllerStack($table_util, $name_util, $path_prefix, true);

        //组合路由信息
        $tips = [];
        $tips[] = "[ TIPS ] Please add routing configuration:";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "[*]routes/web.php:";
        $tips[] = "Route::get('/" . PathUtil::linkPath($path_prefix, $name_util->getUnder(true)) . "', 'App\\Http\\Controllers\\". PathUtil::linkPath($path_prefix, $name_util->getPascal(), "\\") ."Controller@page');";
        $tips[] = "//Route::prefix('/api')->group(function () {";
        $tips[] = "//    Route::resource('/" . PathUtil::linkPath($path_prefix, $name_util->getUnder(true)) . "', App\\Http\\Controllers\\" . PathUtil::linkPath($path_prefix, $name_util->getPascal(), "\\") . "Controller::class);";
        $tips[] = "//});";
        $tips[] = "";
        $tips[] = "[*]routes/api.php:";
        $tips[] = "Route::resource('/" . PathUtil::linkPath($path_prefix, $name_util->getUnder(true)) . "', App\\Http\\Controllers\\" . PathUtil::linkPath($path_prefix, $name_util->getPascal(), "\\") . "Controller::class);";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $message = implode("\n", $tips);

        //输出路由信息
        $this->warn($message);
    }
}
