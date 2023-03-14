<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Lcg\Console\Tasks\GeneratorController;
use Lcg\Console\Tasks\GeneratorModel;
use Lcg\Console\Tasks\GeneratorView;
use Lcg\Models\Curd;
use Lcg\Models\Table;

class CurdCommand extends Command
{
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
        $table_name = $this->argument("table");

        //模型工具
        $table_model = null;
        try{
            $table_model = new Table($table_name);
        }
        catch (\Exception $e){
            $this->error("[ERROR] ".$e->getMessage());
        }

        //构建工具
        $curd_util = new Curd($table_name, $this->option('path'));

        //判断风格
        if($this->option('style') == "2"){
            //创建模型
            GeneratorModel::run($this, $table_model, $curd_util, true);
            //创建视图
            GeneratorView::run($this, $table_model, $curd_util, 2,true);
            //创建控制器
            GeneratorController::run($this, $table_model, $curd_util, 2,true);
        } else {
            //创建模型
            GeneratorModel::run($this, $table_model, $curd_util, true);
            //创建视图
            GeneratorView::run($this, $table_model, $curd_util, 1,true);
            //创建控制器
            GeneratorController::run($this, $table_model, $curd_util, 1,true);
        }

        //输出提示
        //$this->showTips($curd_util);

        //添加路由
        $this->addRoute($curd_util);
    }

    /**
     * 配置提示
     *
     * @param Curd $curd_util
     * @return void
     */
    protected function showTips(Curd $curd_util){
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

    /**
     * 添加路由
     *
     * @param Curd $curd_util
     * @return void
     */
    protected function addRoute(Curd $curd_util){
        //询问添加
        $ask = $this->choice('[CHOICE] 是否添加路由配置', ['Yes', 'No']);
        if($ask === 'No')
            return;

        //路由文件
        $path = base_path("/routes/web.php");
        $content = file_get_contents($path);

        //判断存在
        if(stripos($content, "//{$curd_util->getModelName()}") > 0){
            $this->error("[ TIPS ] 路由已存在\n");
            return;
        }

        //添加路由
        $rows = [];
        $rows[] = "//{$curd_util->getModelName()}";
        $rows[] = "Route::prefix('/{$curd_util->getUrl()}')->group(function () {";
        $rows[] = "    Route::get('/', [{$curd_util->getControllerClassname()}::class, 'page']);";
        $rows[] = "    Route::resource('/interface', {$curd_util->getControllerClassname()}::class);";
        $rows[] = "});";
        $route = implode("\n", $rows);

        //合并内容
        $content = $content . "\n" . $route;

        //保存文件
        file_put_contents($path, $content);

        //提示信息
        $this->info("[ TIPS ] 路由已添加\n");
    }
}
