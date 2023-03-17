<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Lcg\Console\Tasks\GeneratorController;
use Lcg\Console\Tasks\GeneratorModel;
use Lcg\Models\Curd;
use Lcg\Models\Table;

class ApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcg:api {table : Table name}
                            {--path= : Generate path}
                            {--cover=n : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate api of table';

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
        $curd_model = new Curd($table_name, $this->option('path'));

        //获取选项
        $cover = $this->option('cover') == "y";

        //生成代码
        $this->generatorCodes($table_model, $curd_model, $cover);

        //生成路由
        $this->generatorRoute($curd_model);

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
     * @param bool $cover
     * @return void
     */
    protected function generatorCodes(Table $table, Curd $curd, bool $cover=false){
        //提示信息
        $this->components->info('生成相关文件:');

        //创建模型
        GeneratorModel::run($this->components, $table, $curd, [
            "cover" => $cover
        ]);

        //创建控制器
        GeneratorController::run($this->components, $table, $curd, [
            "type" => "api",
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
        $path = base_path("routes/api.php");
        $content = file_get_contents($path);

        //判断存在
        if(stripos($content, "//{$curd->getModelName()}\n") > 0){
            $this->components->twoColumnDetail("<fg=#FFC125>MODIFY</> $path", "<fg=yellow;options=bold>EXIST</>");
            return;
        }

        //添加路由
        $rows = [];
        $rows[] = "//{$curd->getModelName()}";
        $rows[] = "Route::resource('{$curd->getUrl()}', {$curd->getControllerClassname()}::class);";
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
        $tips[] = "[*]routes/api.php:";
        $tips[] = "Route::resource('{$curd->getUrl()}', {$curd->getControllerClassname()}::class);";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $message = implode("\n", $tips);

        //输出路由信息
        $this->warn($message);
    }
}
