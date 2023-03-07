<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Lcg\Console\Stacks\CurdGeneratorStacks;
use Lcg\Console\Tasks\GeneratorController;
use Lcg\Console\Tasks\GeneratorModel;
use Lcg\Console\Tasks\GeneratorView;
use Lcg\Utils\CurdUtil;
use Lcg\Utils\TableUtil;

class CurdCommand extends Command
{
    //use CurdGeneratorStacks;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcg:curd {table : Table name}
                            {--path= : Generate path}
                            {--cover=n : Overwrite existing file}
                            {--style=2 : [1] is single file, [2] is multiple files}';

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

        //模型工具
        $table_util = null;
        try{
            $table_util = new TableUtil($table);
        }
        catch (\Exception $e){
            $this->error("[ERROR] ".$e->getMessage());
        }

        //构建工具
        $curd_util = new CurdUtil($table, $this->option('path'));

        //判断风格
        if($this->option('style') == "2"){
            //创建模型
            GeneratorModel::run($this, $table_util, $curd_util, true);
            //创建视图
            GeneratorView::run($this, $table_util, $curd_util, 2,true);
            //创建控制器
            GeneratorController::run($this, $table_util, $curd_util, 2,true);
        } else {
            //创建模型
            GeneratorModel::run($this, $table_util, $curd_util, true);
            //创建视图
            GeneratorView::run($this, $table_util, $curd_util, 1,true);
            //创建控制器
            GeneratorController::run($this, $table_util, $curd_util, 1,true);
        }

        //输出提示
        $this->showTips($curd_util);
    }

    /**
     * 输出提示信息
     *
     * @param CurdUtil $curd_util
     * @return void
     */
    protected function showTips(CurdUtil $curd_util){
        //组合路由信息
        $tips = [];
        $tips[] = "[ TIPS ] Please add routing configuration:";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "[*]routes/web.php:";
        $tips[] = "Route::prefix('/{$curd_util->getUrl()}')->group(function () {";
        $tips[] = "    Route::get('/', [{$curd_util->getControllerClassname()}::class, 'page']);";
        $tips[] = "    Route::resource('/interface', {$curd_util->getControllerClassname()}::class);";
        $tips[] = "});";
        $tips[] = "";
        $tips[] = "[*]routes/api.php:";
        $tips[] = "Route::resource('/{$curd_util->getUrl()}', {$curd_util->getControllerClassname()}::class);";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $message = implode("\n", $tips);

        //输出路由信息
        $this->warn($message);
    }
}
