<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Lcg\Console\Models\Table;
use Lcg\Console\Stacks\CurdStacks;

class CurdCommand extends Command
{
    use CurdStacks;

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
        $model = null;
        try{
            $model = new Table($table);
        }catch (\Exception $e){
            $this->error("[ERROR] ".$e->getMessage());
        }

        //创建试图
        $this->makeCurdViewStack($model, true);

        //创建模型
        $this->makeCurdModelStack($model, true);

        //创建控制器
        $this->makeCurdControllerStack($model, true);

        //组合路由信息
        $tips = [];
        $tips[] = "[ TIPS ] Please add routing configuration:";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "[*]routes/web.php:";
        $tips[] = "Route::get('/{$model->table}', 'App\\Http\\Controllers\\{$model->model}Controller@page');";
        $tips[] = "//Route::prefix('/api')->group(function () {";
        $tips[] = "//    Route::resource('/{$model->url}', App\\Http\\Controllers\\{$model->model}Controller::class);";
        $tips[] = "//});";
        $tips[] = "";
        $tips[] = "[*]routes/api.php:";
        $tips[] = "Route::resource('/{$model->url}', App\\Http\\Controllers\\{$model->model}Controller::class);";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $message = implode("\n", $tips);

        //输出路由信息
        $this->warn($message);
    }
}
