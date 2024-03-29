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

        //输出辅助信息
        $this->components->info("角色权限功能模块已安装成功");
        $this->components->warn('请执行[php artisan migrate]命令进行数据库迁移');
        $this->components->warn('可执行[php artisan db:seed --class=RbacSeeder]命令填充测试数据');
    }

    /**
     * 安装文件(通用)
     *
     * @return void
     */
    protected function installCommon(){
        //Model
        (new Filesystem)->ensureDirectoryExists(base_path('app/Expand/Lcg/Rbac/Models'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/app/Expand/Lcg/Rbac/Models', base_path('app/Expand/Lcg/Rbac/Models'));

        //MiddlewareAfter
        (new Filesystem)->ensureDirectoryExists(base_path('app/Http/Middleware'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/app/Http/Middleware', base_path('app/Http/Middleware'));

        //Config
        (new Filesystem)->ensureDirectoryExists(base_path('config'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/config', base_path('config'));

        //Database
        (new Filesystem)->ensureDirectoryExists(base_path('database/migrations'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/database/migrations', base_path('database/migrations'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/database/seeders', base_path('database/seeders'));
    }

    /**
     * 安装文件(单文件风格)
     *
     * @return void
     */
    protected function installStyle1()
    {
        //ViewPath
        $view_path = 'Rbac/Role';

        //Controller
        $content = file_get_contents(__DIR__ . '/../../stubs/rbac/app/Expand/Lcg/Rbac/Controllers/RoleController.php');
        $content = str_replace("__VIEW_PATH__", $view_path, $content);
        (new Filesystem)->ensureDirectoryExists(base_path('app/Expand/Lcg/Rbac/Controllers'));
        file_put_contents(base_path('app/Expand/Lcg/Rbac/Controllers/RoleController.php'), $content);

        //View
        (new Filesystem)->ensureDirectoryExists(base_path('resources/js/Pages/Rbac'));
        (new Filesystem)->copy(__DIR__ . '/../../stubs/rbac/resources/js/Pages/Rbac/Role.vue', base_path('resources/js/Pages/Rbac/Role.vue'));
    }

    /**
     * 安装文件(多文件风格)
     *
     * @return void
     */
    protected function installStyle2()
    {
        //ViewPath
        $view_path = 'Rbac/Role/Index';

        //Controller
        $content = file_get_contents(__DIR__ . '/../../stubs/rbac/app/Expand/Lcg/Rbac/Controllers/RoleController.php');
        $content = str_replace("__VIEW_PATH__", $view_path, $content);
        (new Filesystem)->ensureDirectoryExists(base_path('app/Expand/Lcg/Rbac/Controllers'));
        file_put_contents(base_path('app/Expand/Lcg/Rbac/Controllers/RoleController.php'), $content);

        //View
        (new Filesystem)->ensureDirectoryExists(base_path('resources/js/Pages/Rbac/Role'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/resources/js/Pages/Rbac/Role', base_path('resources/js/Pages/Rbac/Role'));
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
        $rows[] = "    Route::get('/', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'page']);";
        $rows[] = "    Route::resource('/interface', App\Expand\Lcg\Rbac\Controllers\RoleController::class);";
        $rows[] = "    Route::get('/interface/{id}/search', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'userSearch']);";
        $rows[] = "    Route::get('/interface/{id}/users', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'userList']);";
        $rows[] = "    Route::post('/interface/{id}/users', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'userPush']);";
        $rows[] = "    Route::delete('/interface/{id}/users/{user_id}', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'userRemove']);";
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
        $tips[] = "    Route::get('/', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'page']);";
        $tips[] = "    Route::resource('/interface', App\Expand\Lcg\Rbac\Controllers\RoleController::class);";
        $tips[] = "    Route::get('/interface/{id}/search', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'userSearch']);";
        $tips[] = "    Route::get('/interface/{id}/users', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'userList']);";
        $tips[] = "    Route::post('/interface/{id}/users', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'userPush']);";
        $tips[] = "    Route::delete('/interface/{id}/users/{user_id}', [App\Expand\Lcg\Rbac\Controllers\RoleController::class, 'userRemove']);";
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
