<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class RbacCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcg:rbac
                            {--style=1 : [0] is multiple files, [1] is single file}';

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
        //安装相关文件
        $this->installFiles();

        //组合提示信息
        $tips = [];
        $tips[] = "[ TIPS ] Please add routing configuration:";
        $tips[] = "``````````````````````````````````````````````````````````````````";
        $tips[] = "[*]routes/web.php:";
        $tips[] = "//Roles";
        $tips[] = "Route::prefix('/rbac/roles')->group(function () {";
        $tips[] = "    Route::get('/', [App\Http\Controllers\Rbac\RoleController::class, 'page']);";
        $tips[] = "    Route::resource('/interface', App\Http\Controllers\Rbac\RoleController::class);";
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
        $tips[] = '        ["title" => "角色", "url" => url("/rbac/rbac_roles")],';
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

    /**
     * Install Files
     *
     * @return void
     */
    protected function installFiles()
    {
        //Controller
        (new Filesystem)->ensureDirectoryExists(base_path('app/Http/Controllers/Rbac'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/app/Http/Controllers/Rbac', base_path('app/Http/Controllers/Rbac'));

        //Model
        (new Filesystem)->ensureDirectoryExists(base_path('app/Models/Rbac'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/app/Models/Rbac', base_path('app/Models/Rbac'));

        //Config
        (new Filesystem)->ensureDirectoryExists(base_path('config'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/config', base_path('config'));

        //Database
        (new Filesystem)->ensureDirectoryExists(base_path('database/migrations'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/database/migrations', base_path('database/migrations'));

        //View
        (new Filesystem)->ensureDirectoryExists(base_path('resources/js/Pages/Rbac'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/rbac/resources/js/Pages/Rbac', base_path('resources/js/Pages/Rbac'));
    }
}
