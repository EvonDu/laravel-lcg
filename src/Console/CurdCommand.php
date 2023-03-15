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
        $curd_model = new Curd($table_name, $this->option('path'));

        //获取选项
        $cover = $this->option('cover') == "y";
        $style = $this->option('style') == "1" ? 1 : 2;

        //创建模型
        GeneratorModel::run($this, $table_model, $curd_model, $cover);
        //创建视图
        GeneratorView::run($this, $table_model, $curd_model, $style, $cover);
        //创建控制器
        GeneratorController::run($this, $table_model, $curd_model, $style, $cover);

        //输出提示
        //$this->showTips($curd_util);

        //添加路由
        $this->addRoute($curd_model);

        //添加导航
        $this->addNavigation($curd_model);
    }

    /**
     * 配置提示
     *
     * @param Curd $curd
     * @return void
     */
    protected function showTips(Curd $curd){
        //组合路由信息/
        $tips = [];
        $tips[] = "[ TIPS ] Please add routing configuration:";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "[*]routes/web.php:";
        $tips[] = "Route::prefix('{$curd->getUrl()}')->group(function () {";
        $tips[] = "    Route::get('/', [{$curd->getControllerClassname()}::class, 'page']);";
        $tips[] = "    Route::resource('/interface', {$curd->getControllerClassname()}::class);";
        $tips[] = "});";
        $tips[] = "";
        $tips[] = "[*]routes/api.php:";
        $tips[] = "Route::resource('{$curd->getUrl()}', {$curd->getControllerClassname()}::class);";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $message = implode("\n", $tips);

        //输出路由信息
        $this->warn($message);
    }

    /**
     * 添加路由
     *
     * @param Curd $curd
     * @return void
     */
    protected function addRoute(Curd $curd){
        //询问添加
        $ask = $this->choice('[CHOICE] 是否添加路由配置', ['Yes', 'No']);
        if($ask === 'No')
            return;

        //路由文件
        $path = base_path("/routes/web.php");
        $content = file_get_contents($path);

        //判断存在
        if(stripos($content, "//{$curd->getModelName()}") > 0){
            $this->warn("[ TIPS ] 路由已存在\n");
            return;
        }

        //添加路由
        $rows = [];
        $rows[] = "//{$curd->getModelName()}";
        $rows[] = "Route::prefix('{$curd->getUrl()}')->group(function () {";
        $rows[] = "    Route::get('/', [{$curd->getControllerClassname()}::class, 'page']);";
        $rows[] = "    Route::resource('/interface', {$curd->getControllerClassname()}::class);";
        $rows[] = "});";
        $route = implode("\n", $rows);

        //合并内容
        $content = $content . "\n" . $route;

        //保存文件
        file_put_contents($path, $content);

        //提示信息
        $this->info("[ TIPS ] 路由已添加\n");
    }

    /**
     * 添加导航
     *
     * @param Curd $curd
     * @return void
     */
    protected function addNavigation(Curd $curd){
        //询问添加
        $ask = $this->choice('[CHOICE] 是否添加简易导航配置', ['Yes', 'No']);
        if($ask === 'No')
            return;

        //路由文件
        $path = base_path("/routes/navigations.php");
        $content = file_get_contents($path);

        //判断存在
        $url_escape  = str_replace("/", "\\/", $curd->getUrl());
        preg_match_all("/url\([\'|\"]{$url_escape}[\'|\"]\)/", $content, $match_exist);
        if(!empty($match_exist[0])) {
            $this->warn("[ TIPS ] 导航已存在\n");
            return;
        }

        //获取主体
        preg_match_all('/return \[([\s\S]*?)\];/i', $content, $match_content);
        $content_body = isset($match_content[1][0]) ? $match_content[1][0] : "";
        if(empty($content_body)) {
            $this->error("[ERRORS] 获取导航信息失败\n");
            return;
        }

        //添加导航
        $rows = [];
        $rows[] = "    [";
        $rows[] = "        \"title\" => \"{$curd->getModelName()}\",";
        $rows[] = "        \"icon\" => \"fa fa-cube\",";
        $rows[] = "        \"url\" => \"#\",";
        $rows[] = "        \"childList\" => [";
        $rows[] = "            [\"title\" => \"{$curd->getModelName()}\", \"url\" => url(\"{$curd->getUrl()}\")],";
        $rows[] = "        ],";
        $rows[] = "    ],";
        $code = implode("\n", $rows);

        //合并内容
        $content_replace = $content_body . $code . "\n";
        $content = str_replace($content_body, $content_replace, $content);
        file_put_contents($path, $content);

        //提示信息
        $this->info("[ TIPS ] 简易导航已添加\n");
    }
}
