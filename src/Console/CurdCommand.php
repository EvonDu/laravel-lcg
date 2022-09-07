<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Lcg\Console\Models\Table;
use Lcg\Console\Stacks\CurdGeneratorStacks;
use Lcg\Utils\NameUtil;
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
        }catch (\Exception $e){
            $this->error("[ERROR] ".$e->getMessage());
        }

        //名称工具
        $name_util = new NameUtil($table_util->table);

        //创建试图
        $this->curdGeneratorViewStack($table_util, $name_util, true);

        //创建模型
        $this->curdGeneratorModelStack($table_util, $name_util, true);

        //创建控制器
        $this->curdGeneratorControllerStack($table_util, $name_util, true);

        //组合路由信息
        $tips = [];
        $tips[] = "[ TIPS ] Please add routing configuration:";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "[*]routes/web.php:";
        $tips[] = "Route::get('/{$name_util->getUnder(true)}', 'App\\Http\\Controllers\\{$name_util->getPascal()}Controller@page');";
        $tips[] = "//Route::prefix('/api')->group(function () {";
        $tips[] = "//    Route::resource('/{$name_util->getUnder(true)}', App\\Http\\Controllers\\{$name_util->getPascal()}Controller::class);";
        $tips[] = "//});";
        $tips[] = "";
        $tips[] = "[*]routes/api.php:";
        $tips[] = "Route::resource('/{$name_util->getUnder(true)}', App\\Http\\Controllers\\{$name_util->getPascal()}Controller::class);";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $message = implode("\n", $tips);

        //输出路由信息
        $this->warn($message);
    }
}
