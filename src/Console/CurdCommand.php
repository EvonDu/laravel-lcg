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
                            {--name= : Generate name}
                            {--prefix= : Generate prefix}
                            {--fullname= : Generate prefix and name}
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
            $this->components->error($e->getMessage());
            return;
        }

        //构建工具
        $curd_model = new Curd($table_name, [
            "name" => $this->option('name'),
            "prefix" => $this->option('prefix'),
            "fullname" => $this->option('fullname'),
        ]);

        //获取选项
        $cover = $this->option('cover') == "y";
        $style = $this->option('style') == "1" ? 1 : 2;

        //生成代码
        $this->generatorCodes($table_model, $curd_model, $style, $cover);

        //生成路由
        $this->generatorRoute($curd_model);

        //生成导航
        $this->generatorNavigation($curd_model);

        //输出提示
        //$this->showTips($curd_util);

        //输出空行
        $this->line("");
    }

    /**
     * 生成代码
     *
     * @param Table $table
     * @param Curd $curd
     * @param int $style
     * @param bool $cover
     * @return void
     */
    protected function generatorCodes(Table $table, Curd $curd, int $style, bool $cover=false){
        //提示信息
        $this->components->info('生成相关文件:');

        //创建模型
        GeneratorModel::run($this->components, $table, $curd, [
            "cover" => $cover
        ]);

        //创建控制器
        GeneratorController::run($this->components, $table, $curd, [
            "style" => $style,
            "cover" => $cover
        ]);

        //创建视图
        GeneratorView::run($this->components, $table, $curd, [
            "style" => $style,
            "cover" => $cover
        ]);
    }

    /**
     * 生成路由
     *
     * @param Curd $curd
     * @return void
     */
    protected function generatorRoute(Curd $curd){
        //询问添加
        $ask = $this->choice('<bg=blue> INFO </> <fg=default>是否添加路由配置</>', ['Yes', 'No']);
        if($ask === 'No')
            return;

        //路由文件
        $path = base_path("routes/web.php");
        $content = file_get_contents($path);

        //判断存在
        if(stripos($content, "Route::prefix('{$curd->getUrl()}')") > 0){
            $this->components->twoColumnDetail("<fg=#FFC125>MODIFY</> $path", "<fg=yellow;options=bold>EXIST</>");
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

        //添加空行
        if(substr($content, -1) != "\n")
            $content .= "\n";

        //合并内容
        $content = $content . "\n" . $route;

        //保存文件
        file_put_contents($path, $content);

        //提示信息
        $this->components->twoColumnDetail("<fg=#FFC125>MODIFY</> $path", "<fg=green;options=bold>DONE</>");
    }

    /**
     * 生成导航
     *
     * @param Curd $curd
     * @return void
     */
    protected function generatorNavigation(Curd $curd){
        //询问添加
        $ask = $this->choice('<bg=blue> INFO </> <fg=default>是否添加简易导航配置</>', ['Yes', 'No']);
        if($ask === 'No')
            return;

        //路由文件
        $path = base_path("/routes/navigations.php");
        $content = file_get_contents($path);

        //判断存在
        $url_escape  = str_replace("/", "\\/", $curd->getUrl());
        preg_match_all("/url\([\'|\"]{$url_escape}[\'|\"]\)/", $content, $match_exist);
        if(!empty($match_exist[0])) {
            $this->components->twoColumnDetail("<fg=#FFC125>MODIFY</> $path", "<fg=yellow;options=bold>EXIST</>");
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
        $this->components->twoColumnDetail("<fg=#FFC125>MODIFY</> $path", "<fg=green;options=bold>DONE</>");
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
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $message = implode("\n", $tips);

        //输出路由信息
        $this->warn($message);
    }
}
