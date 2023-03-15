<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Lcg\Console\Traits\Installs;

class RbacCommand extends Command
{
    use Installs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcg:rbac
                            {--style=2 : [1] is single file, [2] is multiple files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate files related to permissions';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        //获取选项参数
        $style = $this->option('style') == "1" ? 1 : 2;

        //安装相关文件
        $this->installCommon();
        if($style == 2){
            $this->installStyle2();
        } else {
            $this->installStyle1();
        }

        //设置相关中间件
        $this->installRouteMiddlewareAfter('auth.session', 'auth.permission', '\App\Http\Middleware\AuthenticatePermission::class');

        //生成相关路由
        $this->generatorRoute();

        //生成相关导航
        $this->generatorNavigation();

        //输出占位空行
        $this->line("");
    }

    /**
     * 安装文件(通用)
     *
     * @return void
     */
    protected function installCommon(){
        //Model
        (new Filesystem)->ensureDirectoryExists(base_path('app/Models/Rbac'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/common/app/Models/Rbac', base_path('app/Models/Rbac'));

        //MiddlewareAfter
        (new Filesystem)->ensureDirectoryExists(base_path('app/Http/Middleware'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/common/app/Http/Middleware', base_path('app/Http/Middleware'));

        //Config
        (new Filesystem)->ensureDirectoryExists(base_path('config'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/common/config', base_path('config'));

        //Database
        (new Filesystem)->ensureDirectoryExists(base_path('database/migrations'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/common/database/migrations', base_path('database/migrations'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/common/database/seeders', base_path('database/seeders'));
    }

    /**
     * 安装文件(单文件风格)
     *
     * @return void
     */
    protected function installStyle1()
    {
        //Controller
        (new Filesystem)->ensureDirectoryExists(base_path('app/Http/Controllers/Rbac'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/style1/app/Http/Controllers/Rbac', base_path('app/Http/Controllers/Rbac'));

        //View
        (new Filesystem)->ensureDirectoryExists(base_path('resources/js/Pages/Rbac'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/style1/resources/js/Pages/Rbac', base_path('resources/js/Pages/Rbac'));
    }

    /**
     * 安装文件(多文件风格)
     *
     * @return void
     */
    protected function installStyle2()
    {
        //Controller
        (new Filesystem)->ensureDirectoryExists(base_path('app/Http/Controllers/Rbac'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/style2/app/Http/Controllers/Rbac', base_path('app/Http/Controllers/Rbac'));

        //View
        (new Filesystem)->ensureDirectoryExists(base_path('resources/js/Pages/Rbac'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/style2/resources/js/Pages/Rbac', base_path('resources/js/Pages/Rbac'));
    }

    /**
     * 生成路由
     *
     * @return void
     */
    protected function generatorRoute(){
        //询问添加
        $ask = $this->choice('<bg=blue> INFO </> <fg=default>是否添加路由配置</>', ['Yes', 'No']);
        if($ask === 'No')
            return;

        //路由文件
        $path = base_path("routes/web.php");
        $content = file_get_contents($path);

        //判断存在
        if(stripos($content, "//RBAC") > 0){
            $this->components->twoColumnDetail("<fg=#FFC125>MODIFY</> $path", "<fg=yellow;options=bold>EXIST</>");
            return;
        }

        //添加路由
        $rows = [];
        $rows[] = "//RBAC";
        $rows[] = "Route::prefix('/rbac/roles')->middleware(['auth.permission:ROLE_ALL'])->group(function () {";
        $rows[] = "    Route::get('/', [App\Http\Controllers\Rbac\RoleController::class, 'page']);";
        $rows[] = "    Route::resource('/interface', App\Http\Controllers\Rbac\RoleController::class);";
        $rows[] = "    Route::get('/interface/{id}/search', [App\Http\Controllers\Rbac\RoleController::class, 'userSearch']);";
        $rows[] = "    Route::get('/interface/{id}/users', [App\Http\Controllers\Rbac\RoleController::class, 'userList']);";
        $rows[] = "    Route::post('/interface/{id}/users', [App\Http\Controllers\Rbac\RoleController::class, 'userPush']);";
        $rows[] = "    Route::delete('/interface/{id}/users/{user_id}', [App\Http\Controllers\Rbac\RoleController::class, 'userRemove']);";
        $rows[] = "});";
        $route = implode("\n", $rows);

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
     * @return void
     */
    protected function generatorNavigation(){
        //询问添加
        $ask = $this->choice('<bg=blue> INFO </> <fg=default>是否添加导航配置</>', ['Yes', 'No']);
        if($ask === 'No')
            return;

        //路由文件
        $path = base_path("/routes/navigations.php");
        $content = file_get_contents($path);

        //判断存在
        $url_escape  = "\/rbac\/roles";
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
        $rows[] = "        \"title\" => \"权限认证\",";
        $rows[] = "        \"icon\" => \"fas fa-lock\",";
        $rows[] = "        \"url\" => \"#\",";
        $rows[] = "        \"badge\" => \"System\",";
        $rows[] = "        \"childList\" => [";
        $rows[] = "            [\"title\" => \"角色\", \"url\" => url(\"/rbac/roles\")],";
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
     * @return void
     */
    protected function showTips(){
        //组合提示信息
        $tips = [];
        $tips[] = "[ TIPS ] Please add routing configuration:";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "[*]routes/web.php:";
        $tips[] = "//Roles";
        $tips[] = "Route::prefix('/rbac/roles')->middleware(['auth.permission:ROLE_ALL'])->group(function () {";
        $tips[] = "    Route::get('/', [App\Http\Controllers\Rbac\RoleController::class, 'page']);";
        $tips[] = "    Route::resource('/interface', App\Http\Controllers\Rbac\RoleController::class);";
        $tips[] = "    Route::get('/interface/{id}/search', [App\Http\Controllers\Rbac\RoleController::class, 'userSearch']);";
        $tips[] = "    Route::get('/interface/{id}/users', [App\Http\Controllers\Rbac\RoleController::class, 'userList']);";
        $tips[] = "    Route::post('/interface/{id}/users', [App\Http\Controllers\Rbac\RoleController::class, 'userPush']);";
        $tips[] = "    Route::delete('/interface/{id}/users/{user_id}', [App\Http\Controllers\Rbac\RoleController::class, 'userRemove']);";
        $tips[] = "});";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "";
        $tips[] = "[ TIPS ] Please add navigation:";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "[*]app/Http/Middleware/HandleInertiaRequests.php:";
        $tips[] = "[";
        $tips[] = '    "title" => "权限认证",';
        $tips[] = '    "icon" => "fas fa-lock",';
        $tips[] = '    "url" => "#",';
        $tips[] = '    "badge" => "System",';
        $tips[] = '    "childList" => [';
        $tips[] = '        ["title" => "角色", "url" => url("/rbac/roles")],';
        $tips[] = '    ],';
        $tips[] = '],';
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "";
        $tips[] = "[ TIPS ] Please database migration: `php artisan migrate`";
        $tips[] = "";
        $message = implode("\n", $tips);

        //输出提示信息
        $this->warn($message);
    }
}
